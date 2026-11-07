#!/bin/bash
# =============================================================================
# AL GHIFARI HARAMAIN - AUTOMATED PRE-DEPLOYMENT SNAPSHOT CREATOR (SHA-256 VERIFIED)
# Target Server : AnymHost (cPanel)
# Lokasi Core   : /home/xonkgvsz/al-ghifari-core
# Lokasi Public : /home/xonkgvsz/public_html
# Lokasi Repo   : /home/xonkgvsz/repositories/al-ghifari-haramain
# Lokasi Backup : /home/xonkgvsz/al-ghifari-core-backups/
# =============================================================================

set -e

REPO_PATH="${CUSTOM_REPO_PATH:-/home/xonkgvsz/repositories/al-ghifari-haramain}"
CORE_PATH="${CUSTOM_CORE_PATH:-/home/xonkgvsz/al-ghifari-core}"
PUBLIC_PATH="${CUSTOM_PUBLIC_PATH:-/home/xonkgvsz/public_html}"
BACKUP_BASE="${CUSTOM_BACKUP_BASE:-/home/xonkgvsz/al-ghifari-core-backups}"

# -----------------------------------------------------------------------------
# 1. AUDIT RUANG DISK DINAMIS (DYNAMIC BACKUP SPACE & SAFETY MARGIN)
# -----------------------------------------------------------------------------
echo "============================================================================="
echo " 1. AUDIT RUANG DISK DINAMIS SEBELUM SNAPSHOT"
echo "============================================================================="

/bin/mkdir -p "${BACKUP_BASE}"

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

FREE_MB=$(df -m "${BACKUP_BASE}" 2>/dev/null | tail -1 | awk '{print $4}' || true)
if [ -z "$FREE_MB" ] || ! [[ "$FREE_MB" =~ ^[0-9]+$ ]]; then
    echo "FATAL ERROR: Tidak dapat memperkirakan atau membaca sisa ruang disk di ${BACKUP_BASE} secara memadai!"
    echo "Snapshot dibatalkan demi mencegah disk exhaustion pada server produksi."
    exit 1
fi

echo "-> Estimasi ukuran data snapshot : ${SNAPSHOT_ESTIMATE_MB} MB"
echo "-> Kebutuhan minimal (+ margin)  : ${REQUIRED_SPACE_MB} MB"
echo "-> Sisa ruang disk tersedia      : ${FREE_MB} MB"

if [ "$FREE_MB" -lt "$REQUIRED_SPACE_MB" ]; then
    echo "FATAL ERROR: Ruang disk TIDAK MENCUKUPI untuk membuat snapshot!"
    echo " Dibutuhkan : ${REQUIRED_SPACE_MB} MB (termasuk safety margin ${SAFETY_MARGIN_MB} MB)"
    echo " Tersedia   : ${FREE_MB} MB"
    echo "Snapshot dan deployment dibatalkan sebelum mengubah file produksi."
    exit 1
fi
echo "-> Ruang disk terverifikasi memadai."

# -----------------------------------------------------------------------------
# 2. AUDIT SYMLINK PADA DIREKTORI PRODUKSI YANG DIKELOLA (SYMLINK SAFETY)
# -----------------------------------------------------------------------------
echo ""
echo "============================================================================="
echo " 2. AUDIT SYMLINK PADA DIREKTORI PRODUKSI YANG AKAN DI-SNAPSHOT"
echo "============================================================================="

UNAUTHORIZED_SYMLINKS=0
SCAN_FAILURES=0
check_unauthorized_symlinks() {
    local scan_dir="$1"
    if [ -d "$scan_dir" ]; then
        local found_links
        local find_status=0
        found_links=$(find "$scan_dir" -type l 2>&1) || find_status=$?
        if [ "$find_status" -ne 0 ]; then
            echo "FATAL ERROR: Gagal memindai symbolic link pada direktori: $scan_dir (exit code: $find_status)!"
            [ -n "$found_links" ] && echo "Detail error: $found_links"
            SCAN_FAILURES=$((SCAN_FAILURES + 1))
            return 1
        fi
        if [ -n "$found_links" ]; then
            while IFS= read -r found_link || [ -n "$found_link" ]; do
                [ -z "$found_link" ] && continue
                echo "SECURITY WARNING: Symlink terdeteksi di dalam direktori produksi: $found_link -> $(readlink "$found_link" 2>/dev/null || true)"
                UNAUTHORIZED_SYMLINKS=$((UNAUTHORIZED_SYMLINKS + 1))
            done <<< "$found_links"
        fi
    fi
}

for d in "app" "bootstrap" "config" "database" "resources" "routes" "lang"; do
    check_unauthorized_symlinks "${CORE_PATH}/${d}"
done

for d in "assets" "build"; do
    check_unauthorized_symlinks "${PUBLIC_PATH}/${d}"
done

if [ "$SCAN_FAILURES" -gt 0 ]; then
    echo "FATAL ERROR: Pemindaian symlink gagal pada $SCAN_FAILURES direktori produksi! Snapshot dibatalkan demi keamanan."
    exit 1
fi

if [ "$UNAUTHORIZED_SYMLINKS" -gt 0 ]; then
    echo "FATAL SECURITY ERROR: Ditemukan ${UNAUTHORIZED_SYMLINKS} symbolic link tidak diizinkan di dalam target produksi!"
    echo "Snapshot dibatalkan untuk mencegah cp -R menyalin symlink liar ke dalam backup."
    exit 1
fi
echo "-> Seluruh direktori produksi bersih dari symbolic link tidak sah."

# -----------------------------------------------------------------------------
# 3. GENERASI DIREKTORI UNIK BEBAS KOLISI (COLLISION-PROOF ATOMIC DIRECTORY)
# -----------------------------------------------------------------------------
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
PID=$$
RAND_SUFFIX=$(head -c 4 /dev/urandom 2>/dev/null | od -An -tx1 2>/dev/null | tr -d ' ' || echo "${RANDOM}")
SNAPSHOT_NAME="snapshot_${TIMESTAMP}_${PID}_${RAND_SUFFIX}"
SNAPSHOT_DIR="${BACKUP_BASE}/${SNAPSHOT_NAME}"

echo ""
echo "============================================================================="
echo " 3. MEMBUAT SNAPSHOT PRE-DEPLOYMENT: ${SNAPSHOT_NAME}"
echo " Waktu          : $(date)"
echo " Direktori      : ${SNAPSHOT_DIR}"
echo "============================================================================="

if [ -d "${SNAPSHOT_DIR}" ]; then
    echo "FATAL: Direktori snapshot ${SNAPSHOT_DIR} sudah ada! Mencegah penimpaan backup."
    exit 1
fi

if ! /bin/mkdir "${SNAPSHOT_DIR}"; then
    echo "FATAL: Gagal membuat direktori snapshot unik di ${SNAPSHOT_DIR}!"
    exit 1
fi

/bin/mkdir "${SNAPSHOT_DIR}/core" "${SNAPSHOT_DIR}/public"

# -----------------------------------------------------------------------------
# 4. PEMBUATAN MANIFEST PRE-DEPLOYMENT & INCOMING DEPLOYMENT
# -----------------------------------------------------------------------------
echo "-> Membuat manifest file produksi saat ini..."
> "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt"

record_core_files() {
    local target_dir="$1"
    if [ -d "${CORE_PATH}/${target_dir}" ]; then
        (cd "${CORE_PATH}" && find "${target_dir}" -type f) >> "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt"
    fi
}

record_core_files "app"
record_core_files "bootstrap"
record_core_files "config"
record_core_files "database"
record_core_files "resources"
record_core_files "routes"
record_core_files "lang"

for f in "artisan" "composer.json" "composer.lock" "package.json" "package-lock.json"; do
    if [ -f "${CORE_PATH}/${f}" ]; then
        echo "${f}" >> "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt"
    fi
done

record_public_files() {
    local target_dir="$1"
    if [ -d "${PUBLIC_PATH}/${target_dir}" ]; then
        (cd "${PUBLIC_PATH}" && find "${target_dir}" -type f | sed 's|^|public_html/|') >> "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt"
    fi
}

record_public_files "assets"
record_public_files "build"

for f in "favicon.ico" "robots.txt" ".htaccess" "index.php"; do
    if [ -f "${PUBLIC_PATH}/${f}" ]; then
        echo "public_html/${f}" >> "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt"
    fi
done

sort -u "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt" -o "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt"

echo "-> Membuat manifest file masuk dari repositori..."
> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"

record_repo_files() {
    local target_dir="$1"
    if [ -d "${REPO_PATH}/${target_dir}" ]; then
        (cd "${REPO_PATH}" && find "${target_dir}" -type f) >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"
    fi
}

record_repo_files "app"
record_repo_files "config"
record_repo_files "database"
record_repo_files "resources"
record_repo_files "routes"
record_repo_files "lang"

if [ -f "${REPO_PATH}/bootstrap/app.php" ]; then echo "bootstrap/app.php" >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"; fi
if [ -f "${REPO_PATH}/bootstrap/providers.php" ]; then echo "bootstrap/providers.php" >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"; fi

for f in "artisan" "composer.json" "composer.lock" "package.json" "package-lock.json"; do
    if [ -f "${REPO_PATH}/${f}" ]; then
        echo "${f}" >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"
    fi
done

if [ -d "${REPO_PATH}/public/assets" ]; then
    (cd "${REPO_PATH}/public" && find "assets" -type f | sed 's|^|public_html/|') >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"
fi

if [ -d "${REPO_PATH}/public/build" ]; then
    (cd "${REPO_PATH}/public" && find "build" -type f | sed 's|^|public_html/|') >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"
fi

if [ -f "${REPO_PATH}/public/favicon.ico" ]; then echo "public_html/favicon.ico" >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"; fi
if [ -f "${REPO_PATH}/public/robots.txt" ]; then echo "public_html/robots.txt" >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"; fi
if [ -f "${REPO_PATH}/deployment/cpanel_public_html/.htaccess" ]; then echo "public_html/.htaccess" >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"; fi
if [ -f "${REPO_PATH}/deployment/cpanel_public_html/index.php" ]; then echo "public_html/index.php" >> "${SNAPSHOT_DIR}/incoming_deploy_files.txt"; fi

sort -u "${SNAPSHOT_DIR}/incoming_deploy_files.txt" -o "${SNAPSHOT_DIR}/incoming_deploy_files.txt"

# Analisis file lama (stale files): ada di produksi pre-deploy tetapi tidak ada di incoming repo
comm -23 "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt" "${SNAPSHOT_DIR}/incoming_deploy_files.txt" > "${SNAPSHOT_DIR}/stale_files_detected.txt" || true
STALE_COUNT=$(grep -c '[^[:space:]]' "${SNAPSHOT_DIR}/stale_files_detected.txt" || true)
echo "-> Analisis stale files: ${STALE_COUNT} file lama terdeteksi (tercatat di stale_files_detected.txt)."

# -----------------------------------------------------------------------------
# 5. SALIN KOMPONEN CORE DAN PUBLIC KE SNAPSHOT
# -----------------------------------------------------------------------------
echo "-> Menyalin file core ke snapshot..."
for dir in "app" "bootstrap" "config" "database" "resources" "routes" "lang"; do
    if [ -d "${CORE_PATH}/${dir}" ]; then
        /bin/mkdir -p "${SNAPSHOT_DIR}/core/${dir}"
        /bin/cp -R "${CORE_PATH}/${dir}/." "${SNAPSHOT_DIR}/core/${dir}/"
    fi
done

for f in "artisan" "composer.json" "composer.lock" "package.json" "package-lock.json"; do
    if [ -f "${CORE_PATH}/${f}" ]; then
        /bin/cp "${CORE_PATH}/${f}" "${SNAPSHOT_DIR}/core/${f}"
    fi
done

echo "-> Menyalin file public ke snapshot..."
for dir in "assets" "build"; do
    if [ -d "${PUBLIC_PATH}/${dir}" ]; then
        /bin/mkdir -p "${SNAPSHOT_DIR}/public/${dir}"
        /bin/cp -R "${PUBLIC_PATH}/${dir}/." "${SNAPSHOT_DIR}/public/${dir}/"
    fi
done

for f in "favicon.ico" "robots.txt" ".htaccess" "index.php"; do
    if [ -f "${PUBLIC_PATH}/${f}" ]; then
        /bin/cp "${PUBLIC_PATH}/${f}" "${SNAPSHOT_DIR}/public/${f}"
    fi
done

SNAPSHOT_SYMLINKS=$(find "${SNAPSHOT_DIR}/core" "${SNAPSHOT_DIR}/public" -type l 2>/dev/null | wc -l || echo 0)
if [ "$SNAPSHOT_SYMLINKS" -gt 0 ]; then
    echo "FATAL SECURITY ERROR: Snapshot mengandung ${SNAPSHOT_SYMLINKS} symbolic link! Membatalkan snapshot."
    /bin/rm -rf "${SNAPSHOT_DIR}"
    exit 1
fi

# -----------------------------------------------------------------------------
# 6. VERIFIKASI INTEGRITAS SHA-256 BIT-FOR-BIT
# -----------------------------------------------------------------------------
echo "-> Menjalankan verifikasi integritas bit-for-bit SHA-256..."
> "${SNAPSHOT_DIR}/snapshot_sha256.txt"

SHA256_BIN=$(which sha256sum 2>/dev/null || echo "shasum -a 256")
TOTAL_VERIFIED=0

while IFS= read -r rel_file; do
    [ -z "$rel_file" ] && continue

    if [[ "$rel_file" == public_html/* ]]; then
        SUB_PATH="${rel_file#public_html/}"
        ORIG_FILE="${PUBLIC_PATH}/${SUB_PATH}"
        SNAP_FILE="${SNAPSHOT_DIR}/public/${SUB_PATH}"
    else
        ORIG_FILE="${CORE_PATH}/${rel_file}"
        SNAP_FILE="${SNAPSHOT_DIR}/core/${rel_file}"
    fi

    if [ ! -f "$SNAP_FILE" ]; then
        echo "ERROR INTEGRITAS: File snapshot hilang untuk $rel_file!"
        exit 1
    fi

    ORIG_HASH=$($SHA256_BIN "$ORIG_FILE" | awk '{print $1}')
    SNAP_HASH=$($SHA256_BIN "$SNAP_FILE" | awk '{print $1}')

    if [ "$ORIG_HASH" != "$SNAP_HASH" ]; then
        echo "ERROR INTEGRITAS: Checksum SHA-256 tidak cocok untuk $rel_file!"
        echo " Orig: $ORIG_HASH"
        echo " Snap: $SNAP_HASH"
        exit 1
    fi

    echo "$SNAP_HASH  $rel_file" >> "${SNAPSHOT_DIR}/snapshot_sha256.txt"
    TOTAL_VERIFIED=$((TOTAL_VERIFIED + 1))
done < "${SNAPSHOT_DIR}/pre_deploy_managed_files.txt"

sort -k2 "${SNAPSHOT_DIR}/snapshot_sha256.txt" -o "${SNAPSHOT_DIR}/snapshot_sha256.txt"

if [ "$TOTAL_VERIFIED" -eq 0 ]; then
    echo "FATAL: Tidak ada file produksi terdaftar yang dicadangkan! Batalkan snapshot."
    exit 1
fi

echo "-> Verifikasi integritas sukses: $TOTAL_VERIFIED file terverifikasi bit-for-bit via SHA-256."

# -----------------------------------------------------------------------------
# 7. TULIS MANIFEST AKHIR & PERBARUI SYMLINK POINTER 'latest'
# -----------------------------------------------------------------------------
cat <<EOF > "${SNAPSHOT_DIR}/snapshot_manifest.txt"
AL GHIFARI HARAMAIN - PRE-DEPLOYMENT SNAPSHOT MANIFEST
Snapshot Name       : ${SNAPSHOT_NAME}
Timestamp           : ${TIMESTAMP}
Commit Hash         : $(cd "${REPO_PATH}" && git rev-parse --short HEAD 2>/dev/null || echo "N/A")
Total Verified Files: ${TOTAL_VERIFIED}
Stale Files Count   : ${STALE_COUNT}
Estimated Size (MB) : ${SNAPSHOT_ESTIMATE_MB}
Checksum Algorithm  : SHA-256
Status              : 100% BIT-FOR-BIT VERIFIED & ZERO SYMLINKS
EOF

/bin/ln -sfn "${SNAPSHOT_DIR}" "${BACKUP_BASE}/latest"

echo ">> SNAPSHOT BERHASIL DIBUAT & TERVERIFIKASI DI: ${SNAPSHOT_DIR}"
echo ">> POINTER 'latest' DIARAHKAN KE: $(readlink -f ${BACKUP_BASE}/latest)"
exit 0
