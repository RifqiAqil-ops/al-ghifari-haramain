#!/bin/bash
# =============================================================================
# AL GHIFARI HARAMAIN - HARDENED TEST RESTORE VERIFIER (DATABASE PENGUJIAN)
# Target Server : AnymHost (cPanel)
# Catatan Kritis: HANYA diizinkan ke database 'xonkgvsz_test_restore'.
# DILARANG KERAS: Diarahkan ke database production xonkgvsz_alghifari!
# =============================================================================

umask 077
set -euo pipefail

CORE_PATH="/home/xonkgvsz/al-ghifari-core"

# Argumen input: path file backup dan nama database target pengujian
BACKUP_FILE="${1:-}"
TARGET_TEST_DB="${2:-xonkgvsz_test_restore}"

TMP_BASE_DIR="/home/xonkgvsz"
if [ ! -d "$TMP_BASE_DIR" ] || [ ! -w "$TMP_BASE_DIR" ]; then
    TMP_BASE_DIR="${TMPDIR:-/tmp}"
fi

TMP_RESTORE_CNF=""
TMP_RAW_ENTRIES=""
TMP_MALFORMED=""
TMP_EXPECTED_TABLES=""
TMP_DUP_TABLES=""
TMP_RESTORED_TABLES=""

cleanup() {
    rm -f "${TMP_RESTORE_CNF:-}" \
          "${TMP_RAW_ENTRIES:-}" \
          "${TMP_MALFORMED:-}" \
          "${TMP_EXPECTED_TABLES:-}" \
          "${TMP_DUP_TABLES:-}" \
          "${TMP_RESTORED_TABLES:-}"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
trap 'exit 129' HUP

echo "============================================================================="
echo " AL GHIFARI HARAMAIN - VERIFIKASI RESTORE KE DATABASE PENGUJIAN"
echo " Waktu           : $(date)"
echo " Target Test DB  : ${TARGET_TEST_DB}"
echo " Berkas Backup   : ${BACKUP_FILE}"
echo "============================================================================="

# -----------------------------------------------------------------------------
# 1. VALIDASI KEAMANAN TARGET MUTLAK
# -----------------------------------------------------------------------------
if [ -z "$BACKUP_FILE" ] || [ ! -f "$BACKUP_FILE" ]; then
    echo "FATAL ERROR: Berkas backup tidak ditentukan atau tidak ditemukan: '${BACKUP_FILE}'!" >&2
    exit 1
fi

if [ ! -r "$BACKUP_FILE" ]; then
    echo "FATAL ERROR: Berkas backup '${BACKUP_FILE}' tidak dapat dibaca (permission denied)!" >&2
    exit 1
fi

# A. Validasi format nama database (hanya alfanumerik dan underscore)
if ! [[ "$TARGET_TEST_DB" =~ ^[a-zA-Z0-9_]+$ ]]; then
    echo "FATAL SECURITY ERROR: Format nama database target '${TARGET_TEST_DB}' mengandung karakter ilegal!" >&2
    exit 1
fi

# B. Dilarang keras menargetkan database production
if [ "$TARGET_TEST_DB" = "xonkgvsz_alghifari" ]; then
    echo "FATAL SECURITY ERROR: Target restore DILARANG KERAS diarahkan ke database production 'xonkgvsz_alghifari'!" >&2
    exit 1
fi

# C. Batasi target restore HANYA ke 'xonkgvsz_test_restore'
if [ "$TARGET_TEST_DB" != "xonkgvsz_test_restore" ]; then
    echo "FATAL SECURITY ERROR: Target restore DITOLAK! Skrip ini hanya diizinkan untuk target 'xonkgvsz_test_restore'." >&2
    exit 1
fi

# -----------------------------------------------------------------------------
# 2. VALIDASI PRE-IMPORT METADATA BACKUP (SEBELUM TINDAKAN DATABASE)
# -----------------------------------------------------------------------------
echo "-> Memvalidasi berkas metadata sebelum melakukan tindakan database..."

META_FILE="${BACKUP_FILE%.sql}.meta"

# A. Pemeriksaan ketersediaan dan izin baca file .meta
if [ ! -f "$META_FILE" ]; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Berkas metadata (${META_FILE}) TIDAK DITEMUKAN!" >&2
    echo "Restore pengujian dibatalkan sebelum import SQL untuk mencegah import tanpa baseline verifikasi yang sah." >&2
    echo "STATUS RESTORE: GAGAL (Metadata Tidak Ditemukan)" >&2
    echo "=============================================================================" >&2
    exit 1
fi

if [ ! -r "$META_FILE" ]; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Berkas metadata (${META_FILE}) TIDAK DAPAT DIBACA (Permission Denied)!" >&2
    echo "STATUS RESTORE: GAGAL (Metadata Tidak Dapat Dibaca)" >&2
    echo "=============================================================================" >&2
    exit 1
fi

# B. Validasi database asal pada metadata (Wajib persis xonkgvsz_alghifari)
META_DB_LINES=$(grep -c -E '^database=' "$META_FILE" || true)
if [ "$META_DB_LINES" -ne 1 ]; then
    echo "FATAL ERROR: Baris konfigurasi 'database=' tidak valid atau terduplikasi (${META_DB_LINES} baris) dalam metadata!" >&2
    exit 1
fi
META_DB=$(grep -E '^database=' "$META_FILE" | cut -d '=' -f2- | tr -d '\r')
if [ "$META_DB" != "xonkgvsz_alghifari" ]; then
    echo "FATAL ERROR: Metadata asal database TIDAK VALID! Diharapkan 'xonkgvsz_alghifari', ditemukan '${META_DB}'." >&2
    exit 1
fi

# C. Validasi total_tables (harus bilangan bulat positif)
META_TOTAL_TABLES_LINES=$(grep -c -E '^total_tables=' "$META_FILE" || true)
if [ "$META_TOTAL_TABLES_LINES" -ne 1 ]; then
    echo "FATAL ERROR: Baris konfigurasi 'total_tables=' tidak valid atau terduplikasi (${META_TOTAL_TABLES_LINES} baris) dalam metadata!" >&2
    exit 1
fi
META_TOTAL_TABLES=$(grep -E '^total_tables=' "$META_FILE" | cut -d '=' -f2- | tr -d '\r')
if ! [[ "$META_TOTAL_TABLES" =~ ^[1-9][0-9]*$ ]]; then
    echo "FATAL ERROR: Nilai total_tables ('${META_TOTAL_TABLES}') dalam metadata bukan bilangan bulat positif!" >&2
    exit 1
fi

# D. Validasi bagian [record_counts] dan pemeriksaan entri malformed
if ! grep -q -Fx '[record_counts]' "$META_FILE"; then
    echo "FATAL ERROR: Bagian '[record_counts]' tidak ditemukan dalam berkas metadata!" >&2
    exit 1
fi

TMP_RAW_ENTRIES=$(mktemp "${TMP_BASE_DIR}/.meta_raw_XXXXXX.txt")
awk '/^\[record_counts\]$/ {found=1; next} found && !/^[[:space:]]*(#|$)/ {gsub(/\r$/, ""); print}' "$META_FILE" > "$TMP_RAW_ENTRIES"

TMP_MALFORMED=$(mktemp "${TMP_BASE_DIR}/.meta_malformed_XXXXXX.txt")
grep -v -E '^[a-zA-Z0-9_]+=[0-9]+$' "$TMP_RAW_ENTRIES" > "$TMP_MALFORMED" || true

if [ -s "$TMP_MALFORMED" ]; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Ditemukan baris/entri MALFORMED pada bagian [record_counts] dalam metadata:" >&2
    sed 's/^/  - /' "$TMP_MALFORMED" >&2
    echo "STATUS RESTORE: GAGAL (Entri Metadata Malformed)" >&2
    echo "=============================================================================" >&2
    exit 1
fi

# E. Ekstraksi daftar tabel dan validasi duplikasi tabel
TMP_EXPECTED_TABLES=$(mktemp "${TMP_BASE_DIR}/.exp_tbl_XXXXXX.txt")
cut -d '=' -f1 < "$TMP_RAW_ENTRIES" | sort > "$TMP_EXPECTED_TABLES"

TMP_DUP_TABLES=$(mktemp "${TMP_BASE_DIR}/.dup_tbl_XXXXXX.txt")
cut -d '=' -f1 < "$TMP_RAW_ENTRIES" | sort | uniq -d > "$TMP_DUP_TABLES"
if [ -s "$TMP_DUP_TABLES" ]; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Ditemukan nama tabel DUPLIKAT dalam berkas metadata:" >&2
    sed 's/^/  - /' "$TMP_DUP_TABLES" >&2
    echo "STATUS RESTORE: GAGAL (Tabel Metadata Duplikat)" >&2
    echo "=============================================================================" >&2
    exit 1
fi

# F. Validasi kecocokan jumlah entri tabel dengan nilai total_tables
EXPECTED_TABLE_COUNT=$(wc -l < "$TMP_EXPECTED_TABLES" | tr -d ' ')
if [ "$EXPECTED_TABLE_COUNT" -ne "$META_TOTAL_TABLES" ]; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Jumlah entri tabel (${EXPECTED_TABLE_COUNT}) tidak cocok dengan nilai total_tables (${META_TOTAL_TABLES}) pada metadata!" >&2
    echo "STATUS RESTORE: GAGAL (Jumlah Tabel Metadata Tidak Cocok)" >&2
    echo "=============================================================================" >&2
    exit 1
fi

echo "-> Metadata terverifikasi valid:"
echo "   - Database Asal  : ${META_DB}"
echo "   - Total Tabel    : ${EXPECTED_TABLE_COUNT} tabel (konsisten, unik, tidak ada entri malformed)"

# -----------------------------------------------------------------------------
# 3. ALOKASI KREDENSIAL VIA MKTEMP & LARAVEL RUNTIME
# -----------------------------------------------------------------------------
TMP_RESTORE_CNF=$(mktemp "${TMP_BASE_DIR}/.my_restore_XXXXXX.cnf")
chmod 600 "$TMP_RESTORE_CNF"
TMP_RESTORED_TABLES=$(mktemp "${TMP_BASE_DIR}/.res_tbl_XXXXXX.txt")

php -r '
require "'"${CORE_PATH}"'/vendor/autoload.php";
$app = require "'"${CORE_PATH}"'/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$conn = config("database.connections." . config("database.default"));
$cnfFile = $argv[1];

$cnfContent = "[client]\n" .
              "host=" . ($conn["host"] ?? "localhost") . "\n" .
              "port=" . ($conn["port"] ?? "3306") . "\n" .
              "user=" . ($conn["username"] ?? "") . "\n" .
              "password=" . "\"" . addcslashes($conn["password"] ?? "", "\"\\") . "\"\n";

file_put_contents($cnfFile, $cnfContent);
' "$TMP_RESTORE_CNF"

# -----------------------------------------------------------------------------
# 4. VERIFIKASI KEBERADAAN & HAK AKSES DATABASE PENGUJIAN
# -----------------------------------------------------------------------------
echo "-> Memeriksa keberadaan database pengujian [${TARGET_TEST_DB}]..."
DB_EXISTS=$(mysql --defaults-extra-file="$TMP_RESTORE_CNF" \
                  --skip-column-names \
                  --batch \
                  -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '${TARGET_TEST_DB}';")

if [ -z "$DB_EXISTS" ]; then
    echo "FATAL ERROR: Database pengujian [${TARGET_TEST_DB}] belum dibuat di cPanel MySQL Databases!" >&2
    echo "Silakan buat database pengujian '${TARGET_TEST_DB}' secara manual di cPanel terlebih dahulu." >&2
    exit 1
fi

echo "-> Menguji hak akses user terhadap database pengujian [${TARGET_TEST_DB}]..."
if ! mysql --defaults-extra-file="$TMP_RESTORE_CNF" -e "USE \`${TARGET_TEST_DB}\`;" 2>/dev/null; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: User database tidak memiliki hak akses ke [${TARGET_TEST_DB}]!" >&2
    echo "Pastikan user database telah diberikan hak akses (GRANT ALL PRIVILEGES)" >&2
    echo "pada database '${TARGET_TEST_DB}' melalui menu cPanel MySQL Databases." >&2
    echo "=============================================================================" >&2
    exit 1
fi
echo "-> Hak akses user terverifikasi valid."

# -----------------------------------------------------------------------------
# 5. PRASYARAT KOSONG: PASTIKAN DATABASE PENGUJIAN BENAR-BENAR KOSONG
# -----------------------------------------------------------------------------
echo "-> Memeriksa apakah database pengujian [${TARGET_TEST_DB}] benar-benar kosong..."
EXISTING_TABLES_COUNT=$(mysql --defaults-extra-file="$TMP_RESTORE_CNF" \
                             --skip-column-names \
                             --batch \
                             -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '${TARGET_TEST_DB}' AND TABLE_TYPE = 'BASE TABLE';")

if [ "$EXISTING_TABLES_COUNT" -gt 0 ]; then
    echo "FATAL ERROR: Database pengujian [${TARGET_TEST_DB}] TIDAK KOSONG! Ditemukan ${EXISTING_TABLES_COUNT} tabel eksisting." >&2
    echo "Uji restore dibatalkan demi memastikan kebersihan hasil pengujian." >&2
    echo "Harap kosongkan atau buat ulang database pengujian '${TARGET_TEST_DB}'." >&2
    exit 1
fi
echo "-> Database pengujian terverifikasi 100% kosong (0 tabel)."

# -----------------------------------------------------------------------------
# 6. EKSEKUSI RESTORE KE DATABASE PENGUJIAN
# -----------------------------------------------------------------------------
echo "-> Menjalankan import file SQL ke database pengujian [${TARGET_TEST_DB}]..."
set +e
mysql --defaults-extra-file="$TMP_RESTORE_CNF" "$TARGET_TEST_DB" < "$BACKUP_FILE"
RESTORE_STATUS=$?
set -e

if [ "$RESTORE_STATUS" -ne 0 ]; then
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Import restore GAGAL (exit code: ${RESTORE_STATUS})!" >&2
    echo "PERINGATAN: Database pengujian '${TARGET_TEST_DB}' kemungkinan berisi data parsial/korup." >&2
    echo "Catatan: Database pengujian TIDAK dihapus otomatis agar administrator dapat menginspeksi error." >&2
    echo "=============================================================================" >&2
    exit "$RESTORE_STATUS"
fi

# -----------------------------------------------------------------------------
# 7. REKONSILIASI KELENGKAPAN TABEL & JUMLAH RECORD PASCA-IMPORT
# -----------------------------------------------------------------------------
echo "-> Melakukan rekonsiliasi daftar tabel dan jumlah record..."

# A. Ambil daftar tabel hasil restore
mysql --defaults-extra-file="$TMP_RESTORE_CNF" \
      --skip-column-names \
      --batch \
      -e "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '${TARGET_TEST_DB}' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME;" \
      | sort > "$TMP_RESTORED_TABLES"
RESTORED_TABLE_COUNT=$(wc -l < "$TMP_RESTORED_TABLES" | tr -d ' ')

# B. Identifikasi tabel hilang dan tabel ekstra terhadap baseline metadata
MISSING_TABLES=$(comm -23 "$TMP_EXPECTED_TABLES" "$TMP_RESTORED_TABLES" || true)
EXTRA_TABLES=$(comm -13 "$TMP_EXPECTED_TABLES" "$TMP_RESTORED_TABLES" || true)

TABLE_ERROR=0
if [ -n "$MISSING_TABLES" ]; then
    echo "FATAL ERROR: Ditemukan tabel yang HILANG pada hasil restore:" >&2
    echo "$MISSING_TABLES" | sed 's/^/  - /' >&2
    TABLE_ERROR=1
fi

if [ -n "$EXTRA_TABLES" ]; then
    echo "FATAL ERROR: Ditemukan tabel EKSTRA/TIDAK DIKENAL pada hasil restore:" >&2
    echo "$EXTRA_TABLES" | sed 's/^/  - /' >&2
    TABLE_ERROR=1
fi

if [ "$EXPECTED_TABLE_COUNT" -ne "$RESTORED_TABLE_COUNT" ]; then
    echo "FATAL ERROR: Jumlah total tabel tidak cocok! (Ekspektasi: ${EXPECTED_TABLE_COUNT}, Hasil: ${RESTORED_TABLE_COUNT})" >&2
    TABLE_ERROR=1
fi

if [ "$TABLE_ERROR" -eq 1 ]; then
    echo "=============================================================================" >&2
    echo "STATUS RESTORE: GAGAL (Integritas Daftar Tabel Tidak Cocok)" >&2
    echo "Catatan: Database pengujian [${TARGET_TEST_DB}] TIDAK dihapus otomatis." >&2
    echo "=============================================================================" >&2
    exit 1
fi

# C. Rekonsiliasi jumlah record setiap tabel secara 1:1
RECORD_MISMATCHES=0

echo ""
echo "-----------------------------------------------------------------------------"
printf "%-30s | %-15s | %-15s | %-10s\n" "Nama Tabel" "Record Ekspektasi" "Record Restore" "Status"
echo "-----------------------------------------------------------------------------"

while IFS= read -r tbl || [ -n "$tbl" ]; do
    [ -z "$tbl" ] && continue
    EXPECTED_COUNT=$(grep -E "^${tbl}=" "$TMP_RAW_ENTRIES" | head -n1 | cut -d '=' -f2)
    RESTORE_COUNT=$(mysql --defaults-extra-file="$TMP_RESTORE_CNF" \
                          --skip-column-names \
                          --batch \
                          -e "SELECT COUNT(*) FROM \`${TARGET_TEST_DB}\`.\`${tbl}\`;")

    if [ "$EXPECTED_COUNT" = "$RESTORE_COUNT" ]; then
        STATUS="MATCH"
    else
        STATUS="MISMATCH"
        RECORD_MISMATCHES=$((RECORD_MISMATCHES + 1))
    fi

    printf "%-30s | %-15s | %-15s | %-10s\n" "$tbl" "$EXPECTED_COUNT" "$RESTORE_COUNT" "$STATUS"
done < "$TMP_EXPECTED_TABLES"
echo "-----------------------------------------------------------------------------"

if [ "$RECORD_MISMATCHES" -gt 0 ]; then
    echo "" >&2
    echo "=============================================================================" >&2
    echo "FATAL ERROR: Ditemukan ${RECORD_MISMATCHES} tabel dengan selisih jumlah record!" >&2
    echo "STATUS RESTORE: GAGAL (Record Mismatch)" >&2
    echo "Catatan: Database pengujian [${TARGET_TEST_DB}] TIDAK dihapus otomatis." >&2
    echo "=============================================================================" >&2
    exit 1
fi

echo ""
echo "============================================================================="
echo " HASIL VERIFIKASI RESTORE PENGUJIAN"
echo "============================================================================="
echo "Database Target Uji     : ${TARGET_TEST_DB}"
echo "Jumlah Tabel Terbaca    : ${RESTORED_TABLE_COUNT} / ${EXPECTED_TABLE_COUNT} tabel (100% MATCH)"
echo "Tabel Hilang / Ekstra   : 0 (Nihil)"
echo "Rekonsiliasi Record     : 100% MATCH (Seluruh tabel memiliki jumlah baris identik)"
echo "Status Akhir Uji Coba   : SUKSES (Restore terbukti valid & integritas data terjamin)"
echo "Catatan Keamanan        : Database production [xonkgvsz_alghifari] TETAP UTUH & TIDAK TERSENTUH."
echo "Catatan Pembersihan     : Database pengujian [${TARGET_TEST_DB}] TIDAK dihapus otomatis."
echo "                          Silakan drop database pengujian secara manual di cPanel."
echo "============================================================================="
