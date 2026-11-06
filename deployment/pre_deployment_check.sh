#!/bin/bash
# =============================================================================
# AL GHIFARI HARAMAIN - ADVANCED PRE-DEPLOYMENT AUDIT & INTEGRITY CHECK
# Target Server : AnymHost (cPanel)
# Mode          : 100% READ-ONLY (SIMULASI PENGECEKAN - ZERO DESTRUCTIVE)
# Lokasi Repo   : /home/xonkgvsz/repositories/al-ghifari-haramain
# Lokasi Core   : /home/xonkgvsz/al-ghifari-core
# Lokasi Public : /home/xonkgvsz/public_html
# =============================================================================

set -e

# Default ke mode strict (semua anomali kritis menghasilkan FAIL & abort)
STRICT_MODE=1
for arg in "$@"; do
    if [ "$arg" = "--non-strict" ]; then
        STRICT_MODE=0
    elif [ "$arg" = "--strict" ]; then
        STRICT_MODE=1
    fi
done

REPO_PATH="${CUSTOM_REPO_PATH:-/home/xonkgvsz/repositories/al-ghifari-haramain}"
CORE_PATH="${CUSTOM_CORE_PATH:-/home/xonkgvsz/al-ghifari-core}"
PUBLIC_PATH="${CUSTOM_PUBLIC_PATH:-/home/xonkgvsz/public_html}"

TOTAL_PASS=0
TOTAL_WARN=0
TOTAL_FAIL=0

check_status() {
    local status="$1"
    local message="$2"
    if [ "$status" = "PASS" ]; then
        echo -e " [\033[32mPASS\033[0m] $message"
        TOTAL_PASS=$((TOTAL_PASS + 1))
    elif [ "$status" = "WARN" ]; then
        if [ "$STRICT_MODE" -eq 1 ]; then
            echo -e " [\033[31mFAIL (STRICT)\033[0m] $message"
            TOTAL_FAIL=$((TOTAL_FAIL + 1))
        else
            echo -e " [\033[33mWARN\033[0m] $message"
            TOTAL_WARN=$((TOTAL_WARN + 1))
        fi
    else
        echo -e " [\033[31mFAIL\033[0m] $message"
        TOTAL_FAIL=$((TOTAL_FAIL + 1))
    fi
}

echo "============================================================================="
echo " AL GHIFARI HARAMAIN - PRE-DEPLOYMENT AUDIT (STRICT MODE: $STRICT_MODE)"
echo " Waktu Pemeriksaan : $(date)"
echo " User Host         : $(whoami)"
echo " Target Repo       : $REPO_PATH"
echo " Target Core       : $CORE_PATH"
echo " Target Public     : $PUBLIC_PATH"
echo "============================================================================="

# -----------------------------------------------------------------------------
# 1. AUDIT RUANG DISK DINAMIS & KEBUTUHAN SNAPSHOT
# -----------------------------------------------------------------------------
echo ""
echo "1. MEMERIKSA RUANG DISK PENYIMPANAN DINAMIS..."

CALC_KBYTES=0
for item in "app" "bootstrap" "config" "database" "resources" "routes" "lang" "artisan" "composer.json" "composer.lock" "package.json" "package-lock.json"; do
    if [ -e "${CORE_PATH}/${item}" ]; then
        KB=$(du -sk "${CORE_PATH}/${item}" 2>/dev/null | awk '{print $1}' || echo 0)
        CALC_KBYTES=$((CALC_KBYTES + KB))
    fi
done

for item in "assets" "build" "favicon.ico" "robots.txt" ".htaccess" "index.php"; do
    if [ -e "${PUBLIC_PATH}/${item}" ]; then
        KB=$(du -sk "${PUBLIC_PATH}/${item}" 2>/dev/null | awk '{print $1}' || echo 0)
        CALC_KBYTES=$((CALC_KBYTES + KB))
    fi
done

SNAPSHOT_ESTIMATE_MB=$(( (CALC_KBYTES + 1023) / 1024 ))
SAFETY_MARGIN_MB=50
REQUIRED_SPACE_MB=$(( (SNAPSHOT_ESTIMATE_MB * 2) + SAFETY_MARGIN_MB ))
if [ "$REQUIRED_SPACE_MB" -lt 100 ]; then
    REQUIRED_SPACE_MB=100
fi

FREE_MB=$(df -m /home/xonkgvsz 2>/dev/null | tail -1 | awk '{print $4}' || true)
if [ -n "$FREE_MB" ] && [[ "$FREE_MB" =~ ^[0-9]+$ ]]; then
    if [ "$FREE_MB" -ge "$REQUIRED_SPACE_MB" ]; then
        check_status "PASS" "Sisa ruang disk memadai: ${FREE_MB} MB (Dibutuhkan snapshot + buffer: ${REQUIRED_SPACE_MB} MB)"
    else
        check_status "FAIL" "Sisa ruang disk KRITIS: hanya ${FREE_MB} MB tersedia! Dibutuhkan minimal ${REQUIRED_SPACE_MB} MB."
    fi
else
    check_status "FAIL" "Tidak dapat memperkirakan atau memverifikasi ruang disk minimal secara akurat."
fi

# -----------------------------------------------------------------------------
# 2. AUDIT DIREKTORI & IZIN AKSES (PERMISSIONS)
# -----------------------------------------------------------------------------
echo ""
echo "2. MEMERIKSA DIREKTORI & IZIN AKSES BACA/TULIS..."

if [ -d "$REPO_PATH" ] && [ -r "$REPO_PATH" ]; then
    check_status "PASS" "Direktori repositori ada & readable: $REPO_PATH"
else
    check_status "FAIL" "Direktori repositori TIDAK ditemukan atau tidak readable: $REPO_PATH"
fi

if [ -d "$CORE_PATH" ] && [ -w "$CORE_PATH" ]; then
    check_status "PASS" "Direktori Core produksi ada & writable: $CORE_PATH"
else
    check_status "FAIL" "Direktori Core produksi TIDAK ditemukan atau tidak writable: $CORE_PATH"
fi

if [ -d "$PUBLIC_PATH" ] && [ -w "$PUBLIC_PATH" ]; then
    check_status "PASS" "Direktori Public produksi ada & writable: $PUBLIC_PATH"
else
    check_status "FAIL" "Direktori Public produksi TIDAK ditemukan atau tidak writable: $PUBLIC_PATH"
fi

# -----------------------------------------------------------------------------
# 3. AUDIT SYMLINK PADA AREA YANG DIKELOLA (SYMLINK INTEGRITY)
# -----------------------------------------------------------------------------
echo ""
echo "3. MEMERIKSA ANOMALI SYMBOLIC LINK DI DIREKTORI PRODUKSI & REPO..."

UNAUTH_LINKS=0
scan_links() {
    local d="$1"
    if [ -d "$d" ]; then
        while IFS= read -r l; do
            [ -z "$l" ] && continue
            echo " [ANOMALI] Symlink tidak diizinkan ditemukan: $l -> $(readlink "$l" 2>/dev/null || true)"
            UNAUTH_LINKS=$((UNAUTH_LINKS + 1))
        done < <(find "$d" -type l 2>/dev/null || true)
    fi
}

for d in "app" "bootstrap" "config" "database" "resources" "routes" "lang"; do
    scan_links "${CORE_PATH}/${d}"
    scan_links "${REPO_PATH}/${d}"
done

for d in "assets" "build"; do
    scan_links "${PUBLIC_PATH}/${d}"
    scan_links "${REPO_PATH}/public/${d}"
done

if [ "$UNAUTH_LINKS" -eq 0 ]; then
    check_status "PASS" "Zero symlink tidak sah terdeteksi di seluruh direktori yang dikelola"
else
    check_status "FAIL" "Ditemukan ${UNAUTH_LINKS} symbolic link tidak sah! Berbahaya bagi cp -R dan snapshot."
fi

# -----------------------------------------------------------------------------
# 4. AUDIT FILE KRITIS PRODUKSI (.env, vendor, storage uploads)
# -----------------------------------------------------------------------------
echo ""
echo "4. MEMERIKSA FILE KRITIS PRODUKSI (PROTEKSI DATA)..."

if [ -s "$CORE_PATH/.env" ]; then
    check_status "PASS" "File .env produksi terpasang dan berukuran $(stat -c%s "$CORE_PATH/.env" 2>/dev/null || echo '>0') bytes (isi rahasia terlindungi)"
else
    check_status "FAIL" "File .env produksi TIDAK ditemukan atau kosong! JANGAN DEPLOY!"
fi

if [ -f "$CORE_PATH/vendor/autoload.php" ]; then
    check_status "PASS" "Vendor autoload terpasang ($CORE_PATH/vendor/autoload.php)"
else
    check_status "FAIL" "Vendor produksi hilang! Aplikasi tidak dapat berjalan tanpa vendor."
fi

if [ -d "$CORE_PATH/storage/app/public" ]; then
    check_status "PASS" "Direktori upload storage/app/public ada dan terlindungi"
else
    check_status "FAIL" "Direktori storage/app/public TIDAK ADA! Upload dokumen jamaah tidak memiliki direktori target."
fi

if [ -L "$PUBLIC_PATH/storage" ]; then
    SYMLINK_TARGET=$(readlink "$PUBLIC_PATH/storage" 2>/dev/null || true)
    if [ "$SYMLINK_TARGET" = "$CORE_PATH/storage/app/public" ] || [ "$SYMLINK_TARGET" = "../al-ghifari-core/storage/app/public" ]; then
        check_status "PASS" "Symlink public_html/storage mengarah ke target yang sah: $SYMLINK_TARGET"
    else
        check_status "FAIL" "Symlink public_html/storage SALAH SASARAN ($SYMLINK_TARGET)! Harus mengarah ke $CORE_PATH/storage/app/public."
    fi
else
    check_status "FAIL" "Symlink public_html/storage TIDAK TERPASANG! Dokumen jamaah akan mengembalikan 404."
fi

# -----------------------------------------------------------------------------
# 5. AUDIT DEPENDENSI (COMPOSER.LOCK COMPATIBILITY & SAFE ISOLATION PROCEDURE)
# -----------------------------------------------------------------------------
echo ""
echo "5. MEMERIKSA KESELARASAN DEPENDENSI COMPOSER..."

if [ -f "$REPO_PATH/composer.lock" ] && [ -f "$CORE_PATH/composer.lock" ]; then
    if cmp -s "$REPO_PATH/composer.lock" "$CORE_PATH/composer.lock"; then
        check_status "PASS" "composer.lock repositori selaras 100% dengan vendor produksi"
    else
        check_status "FAIL" "composer.lock repositori BERBEDA dengan produksi! Library baru belum disinkronkan ke vendor produksi. JANGAN menjalankan 'composer install' langsung di live production tanpa isolasi! Prosedur aman: Siapkan vendor di staging/lokal terisolasi dengan versi PHP yang identik, uji kompatibilitas, aktifkan maintenance mode, lalu sinkronkan vendor terverifikasi secara terpisah."
    fi
else
    check_status "FAIL" "File composer.lock tidak ditemukan pada repo atau core!"
fi

# -----------------------------------------------------------------------------
# 6. AUDIT PHP CLI & 12 EKSTENSI WAJIB (DOKUMENTASI WEB VS CLI DISPARITY)
# -----------------------------------------------------------------------------
echo ""
echo "6. MEMERIKSA PHP CLI & 12 EKSTENSI WAJIB..."
echo " [CATATAN ARSITEKTUR] Pemeriksaan ini memvalidasi binary PHP CLI ($PHP_CLI)."
echo " Disparitas Lingkungan: Di cPanel, konfigurasi PHP CLI (php.ini) dapat berbeda"
echo " dari PHP Web (PHP-FPM / MultiPHP Manager / .user.ini). Pastikan ekstensi yang sama"
echo " juga diaktifkan di cPanel > Select PHP Version / MultiPHP INI Editor untuk web request."

PHP_CLI=$(which php 2>/dev/null || echo "/usr/local/bin/php")
if [ ! -x "$PHP_CLI" ]; then
    check_status "FAIL" "Binary PHP CLI ($PHP_CLI) tidak ditemukan atau tidak executable! Tidak dapat melanjutkan."
else
    PHP_VERSION=$($PHP_CLI -r "echo PHP_VERSION;" 2>/dev/null || echo "UNKNOWN")
    check_status "PASS" "PHP CLI terdeteksi di $PHP_CLI (Versi: $PHP_VERSION)"

    PHP_EXTS=("bcmath" "ctype" "curl" "fileinfo" "gd" "json" "mbstring" "openssl" "pdo_mysql" "tokenizer" "xml" "zip")
    for ext in "${PHP_EXTS[@]}"; do
        if $PHP_CLI -m 2>/dev/null | grep -i -q "^$ext$"; then
            check_status "PASS" "Ekstensi PHP CLI: $ext"
        else
            check_status "FAIL" "Ekstensi PHP CLI KRITIS tidak aktif: $ext! Aktifkan di cPanel > Select PHP Version > Extensions."
        fi
    done
fi

# -----------------------------------------------------------------------------
# 7. AUDIT INTEGRITAS BUILD VITE (CSS/JS & MANIFEST.JSON DEEP CHECK)
# -----------------------------------------------------------------------------
echo ""
echo "7. MEMERIKSA VALIDITAS ASET BUILD VITE..."

VITE_MANIFEST="$REPO_PATH/public/build/manifest.json"

if [ -s "$VITE_MANIFEST" ]; then
    VITE_CHECK_RES=$($PHP_CLI -r '
        $mPath = "'"$VITE_MANIFEST"'";
        if (!file_exists($mPath)) { echo "ERR_NO_FILE"; exit(1); }
        $m = json_decode(file_get_contents($mPath), true);
        if (!$m || !isset($m["resources/js/app.js"]) || !isset($m["resources/sass/app.scss"])) { echo "ERR_INVALID_ENTRIES"; exit(2); }
        $bDir = dirname($mPath);
        foreach ($m as $k => $e) {
            if (!isset($e["file"]) || !file_exists($bDir . "/" . $e["file"]) || filesize($bDir . "/" . $e["file"]) === 0) {
                echo "ERR_MISSING_OR_EMPTY_ASSET:" . ($e["file"] ?? "unknown");
                exit(3);
            }
        }
        echo "VALID";
        exit(0);
    ' 2>/dev/null || echo "PHP_EXEC_FAILED")

    if [ "$VITE_CHECK_RES" = "VALID" ]; then
        check_status "PASS" "manifest.json valid & seluruh file fisik aset (CSS/JS/Fonts) terbukti ada di disk dan non-kosong"
    else
        check_status "FAIL" "Integritas aset build Vite GAGAL! Hasil evaluasi: $VITE_CHECK_RES. Jalankan npm run build lokal."
    fi
else
    check_status "FAIL" "Aset build Vite (public/build/manifest.json) TIDAK DITEMUKAN di repositori! Jalankan npm run build lokal dan commit public/build."
fi

# -----------------------------------------------------------------------------
# 8. AUDIT FILE .HTACCESS & PROTEKSI DOKUMEN JAMAAH
# -----------------------------------------------------------------------------
echo ""
echo "8. MEMERIKSA ATURAN PROTEKSI .HTACCESS..."

TEMPLATE_HTACCESS="$REPO_PATH/deployment/cpanel_public_html/.htaccess"
PROD_HTACCESS="$PUBLIC_PATH/.htaccess"

if [ -f "$TEMPLATE_HTACCESS" ]; then
    if grep -q "storage/jamaah_documents" "$TEMPLATE_HTACCESS"; then
        check_status "PASS" "Template repositori memiliki proteksi dokumen jamaah (storage/jamaah_documents/)"
    else
        check_status "FAIL" "Template repositori TIDAK memiliki proteksi dokumen jamaah!"
    fi
else
    check_status "FAIL" "Template .htaccess cPanel tidak ditemukan di $TEMPLATE_HTACCESS"
fi

if [ -f "$PROD_HTACCESS" ]; then
    if grep -q "storage/jamaah_documents" "$PROD_HTACCESS"; then
        check_status "PASS" ".htaccess aktif di produksi saat ini sudah memiliki proteksi dokumen jamaah"
    else
        check_status "FAIL" ".htaccess aktif di produksi TIDAK MEMILIKI proteksi dokumen jamaah! Pembaruan wajib dilakukan."
    fi
    if cmp -s "$TEMPLATE_HTACCESS" "$PROD_HTACCESS"; then
        check_status "PASS" ".htaccess produksi identik dengan template repositori"
    else
        echo " [INFO] .htaccess produksi berbeda dengan template repositori. Backup ber-timestamp akan dibuat sebelum pembaruan."
    fi
fi

# -----------------------------------------------------------------------------
# 9. AUDIT KELENGKAPAN FILE SUMBER REPOSITORI
# -----------------------------------------------------------------------------
echo ""
echo "9. MEMERIKSA KELENGKAPAN SOURCE CODE DI REPOSITORI..."

REQUIRED_FILES=(
    "app/Http/Controllers/Controller.php"
    "bootstrap/app.php"
    "bootstrap/providers.php"
    "config/app.php"
    "config/database.php"
    "config/permission.php"
    "routes/web.php"
    "routes/console.php"
    "artisan"
    "deployment/cpanel_public_html/index.php"
    "deployment/cpanel_public_html/.htaccess"
    "public/favicon.ico"
    "public/robots.txt"
)

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$REPO_PATH/$file" ]; then
        check_status "PASS" "Source file terverifikasi: $file"
    else
        check_status "FAIL" "Source file TIDAK ADA di repo: $file"
    fi
done

# -----------------------------------------------------------------------------
# KESIMPULAN AKHIR
# -----------------------------------------------------------------------------
echo ""
echo "============================================================================="
echo " HASIL AKHIR PRE-DEPLOYMENT AUDIT:"
echo " PASS : $TOTAL_PASS"
echo " WARN : $TOTAL_WARN"
echo " FAIL : $TOTAL_FAIL"
echo "============================================================================="

if [ "$TOTAL_FAIL" -eq 0 ]; then
    echo -e "\033[32m>> STATUS: LINGKUNGAN AMAN. DEPLOYMENT DAPAT DILANJUTKAN. <<\033[0m"
    exit 0
else
    echo -e "\033[31m>> STATUS: DITEMUKAN MASALAH KRITIS ($TOTAL_FAIL FAIL)! DEPLOYMENT HARUS DIBATALKAN. <<\033[0m"
    exit 1
fi
