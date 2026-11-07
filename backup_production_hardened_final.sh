#!/bin/bash
# =============================================================================
# AL GHIFARI HARAMAIN - PRODUCTION DATABASE BACKUP & DEEP AUDIT SCRIPT (HARDENED)
# Target Server : AnymHost (cPanel)
# Target User   : xonkgvsz
# Target DB     : xonkgvsz_alghifari (Wajib Tepat)
# =============================================================================

# 1. Terapkan umask ketat dan mode safe execution
umask 077
set -euo pipefail

CORE_PATH="/home/xonkgvsz/al-ghifari-core"
BACKUP_DIR="/home/xonkgvsz/database_backups"

# Inisialisasi variabel pelacak dan file sementara sebelum alokasi apa pun
TMP_CNF=""
TMP_LIVE_TABLES=""
TMP_DUMP_TABLES=""
TMP_DB_NAME=""
TMP_DUMP_FILE=""
TMP_META_FILE=""
FINAL_BACKUP_FILE=""
FINAL_META_FILE=""
SQL_PUBLISHED=0
META_PUBLISHED=0
BACKUP_SUCCESS=0
MAINTENANCE_ACTIVE=0

# Handler Pembersihan Terpadu & Rollback Publikasi (Trap) - AKTIF SEJAK AWAL
cleanup() {
    local exit_code=$?

    # 1. Pastikan Maintenance Mode Laravel selalu dinonaktifkan kembali
    if [ "${MAINTENANCE_ACTIVE}" -eq 1 ] && [ -f "${CORE_PATH}/artisan" ]; then
        echo "" >&2
        echo "-> [MAINTENANCE CLEANUP] Memulihkan aplikasi dari maintenance mode..." >&2
        php "${CORE_PATH}/artisan" up || true
        MAINTENANCE_ACTIVE=0
    fi

    # 2. Hapus berkas konfigurasi dan inventarisasi sementara
    rm -f "${TMP_CNF:-}" "${TMP_LIVE_TABLES:-}" "${TMP_DUMP_TABLES:-}" "${TMP_DB_NAME:-}"

    # 3. Rollback atomik jika proses belum tuntas sukses (BACKUP_SUCCESS != 1)
    if [ "${BACKUP_SUCCESS}" -ne 1 ]; then
        echo "" >&2
        echo "=============================================================================" >&2
        echo "[CLEANUP & ROLLBACK] Terjadi kegagalan atau interupsi proses (exit code: ${exit_code})." >&2

        # Rollback publikasi jika hanya salah satu berkas yang sempat ter-publish
        if [ "${SQL_PUBLISHED}" -eq 1 ] && [ -n "${FINAL_BACKUP_FILE:-}" ] && [ -f "${FINAL_BACKUP_FILE}" ]; then
            echo "-> Membatalkan publikasi: menghapus berkas SQL final parsial: ${FINAL_BACKUP_FILE}" >&2
            rm -f "${FINAL_BACKUP_FILE}"
            SQL_PUBLISHED=0
        fi

        if [ "${META_PUBLISHED}" -eq 1 ] && [ -n "${FINAL_META_FILE:-}" ] && [ -f "${FINAL_META_FILE}" ]; then
            echo "-> Membatalkan publikasi: menghapus berkas metadata final parsial: ${FINAL_META_FILE}" >&2
            rm -f "${FINAL_META_FILE}"
            META_PUBLISHED=0
        fi

        # Hapus berkas staging sementara
        if [ -n "${TMP_DUMP_FILE:-}" ] && [ -f "${TMP_DUMP_FILE}" ]; then
            echo "-> Menghapus berkas dump staging: ${TMP_DUMP_FILE}" >&2
            rm -f "${TMP_DUMP_FILE}"
        fi
        if [ -n "${TMP_META_FILE:-}" ] && [ -f "${TMP_META_FILE}" ]; then
            echo "-> Menghapus berkas metadata staging: ${TMP_META_FILE}" >&2
            rm -f "${TMP_META_FILE}"
        fi
        echo "=============================================================================" >&2
    fi
    exit "${exit_code}"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
trap 'exit 129' HUP

echo "============================================================================="
echo " AL GHIFARI HARAMAIN - BACKUP DATABASE PRODUCTION ANYMHOST"
echo " Waktu           : $(date)"
echo " Host            : $(hostname)"
echo " cPanel User     : $(whoami)"
echo " Direktori Core  : ${CORE_PATH}"
echo "============================================================================="

# Pastikan direktori backup tersedia sebelum alokasi berkas staging
mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

# Alokasi berkas sementara via mktemp
# Staging file dibuat pada direktori backup agar berada di filesystem yang sama (syarat atomic hardlink)
TMP_CNF=$(mktemp /home/xonkgvsz/.my_dump_XXXXXX.cnf)
chmod 600 "$TMP_CNF"
TMP_LIVE_TABLES=$(mktemp /home/xonkgvsz/.live_tbl_XXXXXX.txt)
TMP_DUMP_TABLES=$(mktemp /home/xonkgvsz/.dump_tbl_XXXXXX.txt)
TMP_DB_NAME=$(mktemp /home/xonkgvsz/.db_name_XXXXXX.txt)
TMP_DUMP_FILE=$(mktemp "${BACKUP_DIR}/.dump_stage_XXXXXX.sql")
chmod 600 "$TMP_DUMP_FILE"
TMP_META_FILE=$(mktemp "${BACKUP_DIR}/.meta_stage_XXXXXX.meta")
chmod 600 "$TMP_META_FILE"

# -----------------------------------------------------------------------------
# 1. EKSTRAKSI KONFIGURASI DATABASE RESMI VIA LARAVEL BOOTSTRAP
# -----------------------------------------------------------------------------
if [ ! -d "${CORE_PATH}" ] || [ ! -f "${CORE_PATH}/bootstrap/app.php" ]; then
    echo "FATAL ERROR: Direktori core Laravel tidak valid pada ${CORE_PATH}!" >&2
    exit 1
fi

php -r '
require "'"${CORE_PATH}"'/vendor/autoload.php";
$app = require "'"${CORE_PATH}"'/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$defaultConn = config("database.default");
$conn = config("database.connections.{$defaultConn}");

if (empty($conn)) {
    fwrite(STDERR, "FATAL: Konfigurasi koneksi database default [{$defaultConn}] tidak ditemukan.\n");
    exit(1);
}

$db   = $conn["database"] ?? "";
$user = $conn["username"] ?? "";
$pass = $conn["password"] ?? "";
$host = $conn["host"] ?? "localhost";
$port = $conn["port"] ?? "3306";

if (empty($db) || empty($user)) {
    fwrite(STDERR, "FATAL: Database atau Username kosong pada konfigurasi Laravel aktif.\n");
    exit(1);
}

$cnfFile = $argv[1];
$nameFile = $argv[2];

// Format client options file dengan escape backslash (\) dan double-quote (") aman
$cnfContent = "[client]\n" .
              "host=" . $host . "\n" .
              "port=" . $port . "\n" .
              "user=" . $user . "\n" .
              "password=" . "\"" . addcslashes($pass, "\"\\") . "\"\n";

if (file_put_contents($cnfFile, $cnfContent) === false) {
    fwrite(STDERR, "FATAL: Gagal menulis berkas kredensial sementara.\n");
    exit(1);
}

if (file_put_contents($nameFile, $db) === false) {
    fwrite(STDERR, "FATAL: Gagal menyimpan nama database sementara.\n");
    exit(1);
}
' "$TMP_CNF" "$TMP_DB_NAME"

DB_NAME=$(cat "$TMP_DB_NAME" | tr -d '\r\n')

# -----------------------------------------------------------------------------
# 2. VALIDASI TARGET DATABASE MUTLAK: xonkgvsz_alghifari
# -----------------------------------------------------------------------------
if [ "$DB_NAME" != "xonkgvsz_alghifari" ]; then
    echo "FATAL ERROR: Konfigurasi aktif database ('${DB_NAME}') BUKAN 'xonkgvsz_alghifari'!" >&2
    exit 1
fi
echo "-> Database Target: ${DB_NAME} (TERVERIFIKASI AKTIF & SAH)"

# -----------------------------------------------------------------------------
# 3. AMBIL DAFTAR TABEL LIVE PRODUCTION
# -----------------------------------------------------------------------------
echo "-> Mengambil inventarisasi tabel aktual dari live database..."
mysql --defaults-extra-file="$TMP_CNF" \
      --skip-column-names \
      --batch \
      -e "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE';" "$DB_NAME" \
      | awk '{print $1}' | sort > "$TMP_LIVE_TABLES"

LIVE_TABLE_COUNT=$(wc -l < "$TMP_LIVE_TABLES" | tr -d ' ')
if [ "$LIVE_TABLE_COUNT" -eq 0 ]; then
    echo "FATAL ERROR: Tidak ditemukan tabel fisik pada database ${DB_NAME}!" >&2
    exit 1
fi
echo "-> Terdeteksi ${LIVE_TABLE_COUNT} tabel live pada database production."

# -----------------------------------------------------------------------------
# 4. AUDIT STORAGE ENGINE TABEL: BLOKIR JIKA DITEMUKAN NON-INNODB
# -----------------------------------------------------------------------------
echo "-> Memeriksa storage engine seluruh tabel..."
NON_INNODB_TABLES=$(mysql --defaults-extra-file="$TMP_CNF" \
      --skip-column-names \
      --batch \
      -e "SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = '${DB_NAME}' AND ENGINE != 'InnoDB';")

if [ -n "$NON_INNODB_TABLES" ]; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Ditemukan tabel non-InnoDB pada database production '${DB_NAME}'!" >&2
    echo "mysqldump dengan --single-transaction DIBATALKAN untuk mencegah inkonsistensi snapshot." >&2
    echo "Daftar tabel non-InnoDB penyebab pembatalan:" >&2
    echo "$NON_INNODB_TABLES" >&2
    echo "=============================================================================" >&2
    exit 1
fi
echo "-> Seluruh ${LIVE_TABLE_COUNT} tabel menggunakan storage engine InnoDB (100% konsisten dengan --single-transaction)."

# -----------------------------------------------------------------------------
# 5. MAINTENANCE WINDOW UNTUK KONSISTENSI SNAPSHOT DAN METADATA
# -----------------------------------------------------------------------------
echo "-> Mengaktifkan Maintenance Window Laravel (Zero Write Window)..."
if [ -f "${CORE_PATH}/artisan" ]; then
    php "${CORE_PATH}/artisan" down --render="errors::503" || true
    MAINTENANCE_ACTIVE=1
    echo "-> Maintenance Window AKTIF (Traffic web diisolasi)."
else
    echo "PERINGATAN: artisan tidak ditemukan. Berjalan tanpa maintenance window."
fi

# -----------------------------------------------------------------------------
# 6. GENERASI METADATA JUMLAH RECORD SETIAP TABEL
# -----------------------------------------------------------------------------
echo "-> Menghitung dan merekam metadata jumlah record aktual per tabel..."
{
    echo "# METADATA BACKUP AL GHIFARI HARAMAIN"
    echo "database=${DB_NAME}"
    echo "created_at=$(date -u +"%Y-%m-%dT%H:%M:%SZ")"
    echo "total_tables=${LIVE_TABLE_COUNT}"
    echo "maintenance_window=$([ "$MAINTENANCE_ACTIVE" -eq 1 ] && echo "ACTIVE" || echo "INACTIVE")"
    echo "[record_counts]"
} > "$TMP_META_FILE"

while IFS= read -r tbl || [ -n "$tbl" ]; do
    [ -z "$tbl" ] && continue
    ROW_COUNT=$(mysql --defaults-extra-file="$TMP_CNF" \
                      --skip-column-names \
                      --batch \
                      -e "SELECT COUNT(*) FROM \`${DB_NAME}\`.\`${tbl}\`;")
    echo "${tbl}=${ROW_COUNT}" >> "$TMP_META_FILE"
done < "$TMP_LIVE_TABLES"
echo "-> Metadata ${LIVE_TABLE_COUNT} tabel berhasil dicatat."

# -----------------------------------------------------------------------------
# 7. PENENTUAN NAMA BERKAS FINAL
# -----------------------------------------------------------------------------
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FINAL_BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_backup_${TIMESTAMP}.sql"
FINAL_META_FILE="${BACKUP_DIR}/${DB_NAME}_backup_${TIMESTAMP}.meta"

echo "-> Berkas Staging SQL : ${TMP_DUMP_FILE}"
echo "-> Berkas Staging Meta: ${TMP_META_FILE}"
echo "-> Target Berkas Final: ${FINAL_BACKUP_FILE}"

# -----------------------------------------------------------------------------
# 8. EKSEKUSI MYSQLDUMP KE BERKAS STAGING
# -----------------------------------------------------------------------------
echo "-> Menjalankan mysqldump production ke berkas staging..."
mysqldump --defaults-extra-file="$TMP_CNF" \
          --single-transaction \
          --quick \
          --routines \
          --triggers \
          --events \
          --hex-blob \
          "$DB_NAME" > "$TMP_DUMP_FILE"

echo "-> mysqldump berhasil diselesaikan (exit code: 0)."

# Segera hapus file kredensial setelah mysqldump selesai
rm -f "$TMP_CNF"

# Setelah mysqldump tuntas, nonaktifkan maintenance window secepatnya
if [ "${MAINTENANCE_ACTIVE}" -eq 1 ] && [ -f "${CORE_PATH}/artisan" ]; then
    echo "-> Menonaktifkan Maintenance Window Laravel (aplikasi live kembali)..."
    php "${CORE_PATH}/artisan" up || true
    MAINTENANCE_ACTIVE=0
fi

# -----------------------------------------------------------------------------
# 9. VERIFIKASI INTEGRITAS STRUKTUR & REKONSILIASI 1:1 TABEL DUMP
# -----------------------------------------------------------------------------
echo "-> Melakukan verifikasi integritas isi berkas dump staging..."

# A. Pemeriksaan ukuran berkas fisik
FILE_SIZE_BYTES=$(wc -c < "$TMP_DUMP_FILE" | tr -d ' ')
FILE_SIZE_HUMAN=$(ls -lh "$TMP_DUMP_FILE" | awk '{print $5}')
if [ "$FILE_SIZE_BYTES" -lt 50000 ]; then
    echo "FATAL ERROR: Ukuran berkas dump (${FILE_SIZE_BYTES} bytes) tidak wajar (terlalu kecil/kosong)!" >&2
    exit 1
fi

# B. Ekstraksi daftar tabel dari berkas dump
grep -E '^CREATE TABLE `' "$TMP_DUMP_FILE" | sed -e 's/CREATE TABLE `//' -e 's/`.*//' | sort > "$TMP_DUMP_TABLES"
DUMP_TABLE_COUNT=$(wc -l < "$TMP_DUMP_TABLES" | tr -d ' ')

# C. Rekonsiliasi 1:1 antara Live Tables vs Dump Tables (diff)
diff -u "$TMP_LIVE_TABLES" "$TMP_DUMP_TABLES" || {
    echo "FATAL ERROR: Daftar tabel pada dump TIDAK COCOK dengan tabel aktual live!" >&2
    exit 1
}

# D. Pemeriksaan data tabel vital
for vital in "users" "company_settings" "roles" "permissions"; do
    if ! grep -q "INSERT INTO \`${vital}\`" "$TMP_DUMP_FILE"; then
        echo "FATAL ERROR: Statement INSERT INTO tidak ditemukan untuk tabel vital '${vital}'!" >&2
        exit 1
    fi
done

# E. Pemeriksaan marker penutup
TAIL_MARKER=$(tail -n 10 "$TMP_DUMP_FILE" | grep "Dump completed" || true)
if [ -z "$TAIL_MARKER" ]; then
    echo "FATAL ERROR: Marker 'Dump completed' tidak ditemukan pada akhir berkas dump!" >&2
    exit 1
fi

# -----------------------------------------------------------------------------
# 10. PUBLIKASI ATOMIK KE TARGET FINAL (DENGAN ROLLBACK STATUS PELACAK)
# -----------------------------------------------------------------------------
echo "-> Seluruh verifikasi internal lolos. Mempublikasikan berkas secara atomik..."

# Publikasi berkas SQL via hard link (ln) pada filesystem yang sama
if ! ln "$TMP_DUMP_FILE" "$FINAL_BACKUP_FILE" 2>/dev/null; then
    echo "FATAL ERROR: Gagal mempublikasikan backup! Berkas tujuan '${FINAL_BACKUP_FILE}' sudah ada atau terjadi kesalahan filesystem." >&2
    exit 1
fi
SQL_PUBLISHED=1

# Publikasi berkas Metadata via hard link (ln)
if ! ln "$TMP_META_FILE" "$FINAL_META_FILE" 2>/dev/null; then
    echo "FATAL ERROR: Gagal mempublikasikan metadata! Berkas metadata tujuan '${FINAL_META_FILE}' sudah ada atau terjadi kesalahan filesystem." >&2
    exit 1
fi
META_PUBLISHED=1

# Bersihkan berkas staging setelah publikasi kedua file berhasil
rm -f "$TMP_DUMP_FILE" "$TMP_META_FILE"

chmod 600 "$FINAL_BACKUP_FILE" "$FINAL_META_FILE"
BACKUP_SUCCESS=1

echo ""
echo "============================================================================="
echo " HASIL AKHIR AUDIT BACKUP PRODUCTION ANYMHOST"
echo "============================================================================="
echo "Target Database          : ${DB_NAME}"
echo "Lokasi File Backup Final : ${FINAL_BACKUP_FILE}"
echo "Lokasi Metadata Final    : ${FINAL_META_FILE}"
echo "Ukuran File              : ${FILE_SIZE_HUMAN} (${FILE_SIZE_BYTES} bytes)"
echo "Exit Code mysqldump      : 0 (Sukses)"
echo "Rekonsiliasi Tabel       : ${DUMP_TABLE_COUNT} / ${LIVE_TABLE_COUNT} tabel (100% MATCH)"
echo "Audit Storage Engine     : InnoDB (100% Verified)"
echo "Audit Statement Data     : VALID (users, company_settings, roles, permissions)"
echo "Pemeriksaan Akhir Dump   : VALID (${TAIL_MARKER})"
echo "Status Berkas Backup     : LENGKAP & TERKUNCI (chmod 600)"
echo "Status Maintenance Window: SUKSES (Zero write window dipulihkan)"
echo "Catatan Penting          : Format skema, data, dan metadata tabel terverifikasi."
echo "                           Untuk pembuktian semantik pemulihan 100%, jalankan uji"
echo "                           restore menggunakan restore_test_database_hardened.sh."
echo "============================================================================="
