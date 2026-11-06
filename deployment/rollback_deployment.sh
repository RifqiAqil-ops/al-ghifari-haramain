#!/bin/bash
# =============================================================================
# AL GHIFARI HARAMAIN - ADVANCED MANIFEST-DRIVEN ROLLBACK CONTROLLER (HARDENED)
# Target Server : AnymHost (cPanel)
# Lokasi Core   : /home/xonkgvsz/al-ghifari-core
# Lokasi Public : /home/xonkgvsz/public_html
# Lokasi Backup : /home/xonkgvsz/al-ghifari-core-backups/
# =============================================================================

set -e

CORE_PATH="${CUSTOM_CORE_PATH:-/home/xonkgvsz/al-ghifari-core}"
PUBLIC_PATH="${CUSTOM_PUBLIC_PATH:-/home/xonkgvsz/public_html}"
BACKUP_BASE="${CUSTOM_BACKUP_BASE:-/home/xonkgvsz/al-ghifari-core-backups}"

CLEAR_VIEWS_FLAG=0
TARGET_SNAPSHOT=""

for arg in "$@"; do
    if [ "$arg" = "--clear-views" ]; then
        CLEAR_VIEWS_FLAG=1
    elif [[ "$arg" != --* ]] && [ -z "$TARGET_SNAPSHOT" ]; then
        TARGET_SNAPSHOT="$arg"
    fi
done

if [ -z "$TARGET_SNAPSHOT" ]; then
    TARGET_SNAPSHOT="${BACKUP_BASE}/latest"
fi

echo "============================================================================="
echo " AL GHIFARI HARAMAIN - ADVANCED MANIFEST-DRIVEN ROLLBACK"
echo " Waktu Rollback   : $(date)"
echo " Target Snapshot  : ${TARGET_SNAPSHOT}"
echo " Clear Views Flag : ${CLEAR_VIEWS_FLAG}"
echo "============================================================================="

# -----------------------------------------------------------------------------
# 1. AUDIT AWAL DIREKTORI SNAPSHOT & DETEKSI SYMLINK
# -----------------------------------------------------------------------------
if [ ! -d "${TARGET_SNAPSHOT}/core" ] || [ ! -d "${TARGET_SNAPSHOT}/public" ]; then
    echo "FATAL ERROR: Snapshot tidak valid atau direktori core/public tidak lengkap di: ${TARGET_SNAPSHOT}"
    echo "Daftar snapshot yang tersedia di server:"
    ls -ld ${BACKUP_BASE}/snapshot_* 2>/dev/null || echo "Tidak ada snapshot ditemukan."
    exit 1
fi

SNAPSHOT_SYMLINKS=$(find "${TARGET_SNAPSHOT}/core" "${TARGET_SNAPSHOT}/public" -type l 2>/dev/null | wc -l || echo 0)
if [ "$SNAPSHOT_SYMLINKS" -gt 0 ]; then
    echo "FATAL SECURITY ERROR: Snapshot mengandung ${SNAPSHOT_SYMLINKS} symbolic link! Rollback dibatalkan demi keamanan."
    exit 1
fi

PRE_DEPLOY_MANIFEST="${TARGET_SNAPSHOT}/pre_deploy_managed_files.txt"
INCOMING_MANIFEST="${TARGET_SNAPSHOT}/incoming_deploy_files.txt"
SHA256_FILE="${TARGET_SNAPSHOT}/snapshot_sha256.txt"

# -----------------------------------------------------------------------------
# 2. VALIDASI KETAT MANIFEST WAJIB (PRE-DEPLOY, INCOMING, SHA-256)
# Seluruh manifest bersifat WAJIB ada, non-kosong, valid format, dan bebas duplikasi.
# -----------------------------------------------------------------------------
if [ ! -s "$PRE_DEPLOY_MANIFEST" ]; then
    echo "FATAL ERROR: File manifest pre-deploy tidak ditemukan atau kosong di: $PRE_DEPLOY_MANIFEST"
    exit 1
fi

if [ ! -s "$INCOMING_MANIFEST" ]; then
    echo "FATAL ERROR: File incoming_deploy_files.txt WAJIB ada dan tidak boleh kosong di: $INCOMING_MANIFEST!"
    echo "Rollback dibatalkan demi keamanan sebelum menyentuh file produksi."
    exit 1
fi

if [ ! -s "$SHA256_FILE" ]; then
    echo "FATAL ERROR: File checksum SHA-256 tidak ditemukan atau kosong di: $SHA256_FILE! Rollback dibatalkan demi keamanan."
    exit 1
fi

CORE_CANON_BASE=$(readlink -f "$CORE_PATH" 2>/dev/null || realpath "$CORE_PATH" 2>/dev/null || true)
PUB_CANON_BASE=$(readlink -f "$PUBLIC_PATH" 2>/dev/null || realpath "$PUBLIC_PATH" 2>/dev/null || true)

if [ -z "$CORE_CANON_BASE" ] || [ -z "$PUB_CANON_BASE" ]; then
    echo "FATAL SECURITY ERROR: Gagal menentukan canonical path untuk CORE ($CORE_PATH) atau PUBLIC ($PUBLIC_PATH)!"
    exit 1
fi

verify_canonical_containment() {
    local target_path="$1"
    local allowed_canon_base="$2"

    local current_check="$target_path"
    while [ ! -e "$current_check" ] && [ "$current_check" != "/" ] && [ "$current_check" != "." ]; do
        current_check=$(dirname "$current_check")
    done

    local resolved_path
    resolved_path=$(readlink -f "$current_check" 2>/dev/null || realpath "$current_check" 2>/dev/null || true)

    if [ -z "$resolved_path" ]; then
        echo "SECURITY ERROR: Gagal me-resolve canonical path untuk: $current_check"
        return 1
    fi

    if [ "$resolved_path" != "$allowed_canon_base" ] && [[ "$resolved_path" != "$allowed_canon_base"/* ]]; then
        echo "SECURITY ERROR: SYMLINK ESCAPE / PATH TRAVERSAL TERDETEKSI!"
        echo " Path asli      : $target_path"
        echo " Canonical path : $resolved_path"
        echo " Batas aman     : $allowed_canon_base"
        return 1
    fi

    return 0
}

validate_manifest_syntax_and_whitelist() {
    local entry="$1"
    local source_name="$2"

    if [ -z "$entry" ]; then
        echo "SECURITY ERROR: Entri baris kosong ditemukan di manifest $source_name!"
        return 1
    fi

    if [[ "$entry" == /* ]] || [[ "$entry" == \\* ]] || [[ "$entry" == *:* ]]; then
        echo "SECURITY ERROR: Path absolut atau format tidak aman di $source_name: '$entry'!"
        return 1
    fi

    if [[ "$entry" == *".."* ]]; then
        echo "SECURITY ERROR: Directory traversal (..) di $source_name: '$entry'!"
        return 1
    fi

    if [[ "$entry" == public_html/* ]]; then
        local sub="${entry#public_html/}"
        if [[ "$sub" != assets/* ]] && [[ "$sub" != build/* ]] && [[ "$sub" != "favicon.ico" ]] && [[ "$sub" != "robots.txt" ]] && [[ "$sub" != ".htaccess" ]] && [[ "$sub" != "index.php" ]]; then
            echo "SECURITY ERROR: Path publik di luar whitelist resmi di $source_name: '$entry'!"
            return 1
        fi
        verify_canonical_containment "${PUBLIC_PATH}/${sub}" "$PUB_CANON_BASE" || return 1
    else
        if [[ "$entry" != app/* ]] && [[ "$entry" != bootstrap/* ]] && [[ "$entry" != config/* ]] && [[ "$entry" != database/* ]] && [[ "$entry" != resources/* ]] && [[ "$entry" != routes/* ]] && [[ "$entry" != lang/* ]] && [[ "$entry" != "artisan" ]] && [[ "$entry" != "composer.json" ]] && [[ "$entry" != "composer.lock" ]] && [[ "$entry" != "package.json" ]] && [[ "$entry" != "package-lock.json" ]]; then
            echo "SECURITY ERROR: Path core di luar whitelist resmi di $source_name: '$entry'!"
            return 1
        fi
        verify_canonical_containment "${CORE_PATH}/${entry}" "$CORE_CANON_BASE" || return 1
    fi

    return 0
}

# -----------------------------------------------------------------------------
# 3. VALIDASI INCOMING DEPLOY MANIFEST SECARA KETAT & ANTI-DUPLIKASI
# -----------------------------------------------------------------------------
echo ""
echo "1. MEMVALIDASI INCOMING MANIFEST (SYNTAX, WHITELIST, CONTAINMENT, ANTI-DUPLIKASI)..."

TEMP_INC_PATHS=$(mktemp)
INCOMING_COUNT=0

while IFS= read -r inc_entry || [ -n "$inc_entry" ]; do
    [ -z "$inc_entry" ] && continue
    validate_manifest_syntax_and_whitelist "$inc_entry" "incoming_deploy_files.txt" || {
        rm -f "$TEMP_INC_PATHS"
        exit 1
    }
    echo "$inc_entry" >> "$TEMP_INC_PATHS"
    INCOMING_COUNT=$((INCOMING_COUNT + 1))
done < "$INCOMING_MANIFEST"

if [ "$INCOMING_COUNT" -eq 0 ]; then
    echo "FATAL SECURITY ERROR: incoming_deploy_files.txt tidak memiliki entri valid! Rollback dibatalkan."
    rm -f "$TEMP_INC_PATHS"
    exit 1
fi

INC_DUPLICATES=$(sort "$TEMP_INC_PATHS" | uniq -d || true)
if [ -n "$INC_DUPLICATES" ]; then
    echo "FATAL SECURITY ERROR: Ditemukan entri path duplikat di incoming_deploy_files.txt!"
    echo "$INC_DUPLICATES"
    rm -f "$TEMP_INC_PATHS"
    exit 1
fi
rm -f "$TEMP_INC_PATHS"
echo "-> incoming_deploy_files.txt valid 100% ($INCOMING_COUNT entri bersih)."

# -----------------------------------------------------------------------------
# 4. VALIDASI CHECKSUM MANIFEST SECARA PERSIS (EXACT 1-TO-1 MATCH)
# -----------------------------------------------------------------------------
echo ""
echo "2. MEMVALIDASI KONSISTENSI CHECKSUM MANIFEST (EXACT 1-TO-1 MATCH)..."

TEMP_PRE_PATHS=$(mktemp)
TEMP_SHA_PATHS=$(mktemp)

grep -v '^[[:space:]]*$' "$PRE_DEPLOY_MANIFEST" | sort > "$TEMP_PRE_PATHS"

while IFS=' ' read -r hash_val rel_path || [ -n "$hash_val" ]; do
    [ -z "$hash_val" ] && continue
    [ -z "$rel_path" ] && continue

    validate_manifest_syntax_and_whitelist "$rel_path" "snapshot_sha256.txt" || {
        rm -f "$TEMP_PRE_PATHS" "$TEMP_SHA_PATHS"
        exit 1
    }
    echo "$rel_path" >> "$TEMP_SHA_PATHS"
done < "$SHA256_FILE"

SHA_DUPLICATES=$(sort "$TEMP_SHA_PATHS" | uniq -d || true)
if [ -n "$SHA_DUPLICATES" ]; then
    echo "FATAL SECURITY ERROR: Ditemukan entri path duplikat di snapshot_sha256.txt!"
    echo "$SHA_DUPLICATES"
    rm -f "$TEMP_PRE_PATHS" "$TEMP_SHA_PATHS"
    exit 1
fi

sort "$TEMP_SHA_PATHS" -o "$TEMP_SHA_PATHS"

if ! cmp -s "$TEMP_PRE_PATHS" "$TEMP_SHA_PATHS"; then
    echo "FATAL INTEGRITY ERROR: Ketidaksesuaian persis antara pre_deploy_managed_files.txt dan snapshot_sha256.txt!"
    echo "Perbedaan entri (diff -u):"
    diff -u "$TEMP_PRE_PATHS" "$TEMP_SHA_PATHS" || true
    rm -f "$TEMP_PRE_PATHS" "$TEMP_SHA_PATHS"
    exit 1
fi
rm -f "$TEMP_PRE_PATHS" "$TEMP_SHA_PATHS"

echo "-> Daftar path checksum selaras 100% dan identik dengan manifest pre-deploy."

# -----------------------------------------------------------------------------
# 5. VERIFIKASI INTEGRITAS BIT-FOR-BIT SHA-256 FILE SNAPSHOT
# -----------------------------------------------------------------------------
echo ""
echo "3. MEMVERIFIKASI INTEGRITAS BIT-FOR-BIT SELURUH FILE SNAPSHOT..."

SHA256_BIN=$(which sha256sum 2>/dev/null || echo "shasum -a 256")
SNAPSHOT_VERIFIED_COUNT=0

while IFS=' ' read -r expected_hash rel_path || [ -n "$expected_hash" ]; do
    [ -z "$expected_hash" ] && continue
    [ -z "$rel_path" ] && continue

    if [[ "$rel_path" == public_html/* ]]; then
        SUB_PATH="${rel_path#public_html/}"
        SNAP_FILE="${TARGET_SNAPSHOT}/public/${SUB_PATH}"
    else
        SNAP_FILE="${TARGET_SNAPSHOT}/core/${rel_path}"
    fi

    if [ ! -f "$SNAP_FILE" ]; then
        echo "FATAL INTEGRITY ERROR: File snapshot hilang untuk: $rel_path ($SNAP_FILE)!"
        exit 1
    fi

    ACTUAL_HASH=$($SHA256_BIN "$SNAP_FILE" | awk '{print $1}')
    if [ "$ACTUAL_HASH" != "$expected_hash" ]; then
        echo "FATAL INTEGRITY ERROR: Checksum SHA-256 snapshot rusak/berbeda untuk: $rel_path!"
        echo " Expected: $expected_hash"
        echo " Actual  : $ACTUAL_HASH"
        exit 1
    fi

    SNAPSHOT_VERIFIED_COUNT=$((SNAPSHOT_VERIFIED_COUNT + 1))
done < "$SHA256_FILE"

echo "-> Seluruh file snapshot ($SNAPSHOT_VERIFIED_COUNT file) terbukti valid bit-for-bit."

# -----------------------------------------------------------------------------
# 6. ROLLBACK PREFLIGHT: VALIDASI IZIN TULIS (WRITABILITY) SELURUH TARGET
# Menjamin target direktori dan file produksi writable SEBELUM eksekusi apa pun.
# -----------------------------------------------------------------------------
echo ""
echo "4. MENJALANKAN ROLLBACK PREFLIGHT AUDIT (WRITABILITY & CONTAINMENT)..."

if [ ! -w "$CORE_PATH" ]; then
    echo "FATAL PREFLIGHT ERROR: Direktori Core ($CORE_PATH) tidak memiliki izin tulis (write permission)!"
    exit 1
fi

if [ ! -w "$PUBLIC_PATH" ]; then
    echo "FATAL PREFLIGHT ERROR: Direktori Public ($PUBLIC_PATH) tidak memiliki izin tulis (write permission)!"
    exit 1
fi

# Preflight: periksa seluruh file yang akan dihapus (incoming non-predeploy)
while IFS= read -r f || [ -n "$f" ]; do
    [ -z "$f" ] && continue
    if ! grep -Fxq "$f" "$PRE_DEPLOY_MANIFEST"; then
        if [[ "$f" == public_html/* ]]; then
            T_FILE="${PUBLIC_PATH}/${f#public_html/}"
            B_CANON="$PUB_CANON_BASE"
        else
            T_FILE="${CORE_PATH}/${f}"
            B_CANON="$CORE_CANON_BASE"
        fi

        verify_canonical_containment "$T_FILE" "$B_CANON" || {
            echo "FATAL PREFLIGHT ERROR: Containment gagal pada target cleanup: $T_FILE"
            exit 1
        }

        # Jika file ada, pastikan writable
        if [ -e "$T_FILE" ] && [ ! -w "$T_FILE" ]; then
            echo "FATAL PREFLIGHT ERROR: Target cleanup file tidak writable: $T_FILE"
            exit 1
        fi
        # Pastikan parent dir writable
        P_DIR=$(dirname "$T_FILE")
        if [ -e "$P_DIR" ] && [ ! -w "$P_DIR" ]; then
            echo "FATAL PREFLIGHT ERROR: Parent direktori cleanup tidak writable: $P_DIR"
            exit 1
        fi
    fi
done < "$INCOMING_MANIFEST"

# Preflight: periksa seluruh file yang akan direstore dari snapshot
while IFS= read -r f || [ -n "$f" ]; do
    [ -z "$f" ] && continue
    if [[ "$f" == public_html/* ]]; then
        T_FILE="${PUBLIC_PATH}/${f#public_html/}"
        B_CANON="$PUB_CANON_BASE"
        S_FILE="${TARGET_SNAPSHOT}/public/${f#public_html/}"
    else
        T_FILE="${CORE_PATH}/${f}"
        B_CANON="$CORE_CANON_BASE"
        S_FILE="${TARGET_SNAPSHOT}/core/${f}"
    fi

    verify_canonical_containment "$T_FILE" "$B_CANON" || {
        echo "FATAL PREFLIGHT ERROR: Containment gagal pada target restore: $T_FILE"
        exit 1
    }

    if [ ! -f "$S_FILE" ]; then
        echo "FATAL PREFLIGHT ERROR: File sumber snapshot tidak ada: $S_FILE"
        exit 1
    fi

    if [ -e "$T_FILE" ] && [ ! -w "$T_FILE" ]; then
        echo "FATAL PREFLIGHT ERROR: Target restore file yang sudah ada tidak writable: $T_FILE"
        exit 1
    fi

    # Cari ancestor directory terdekat yang ada dan pastikan writable
    A_DIR=$(dirname "$T_FILE")
    while [ ! -e "$A_DIR" ] && [ "$A_DIR" != "/" ] && [ "$A_DIR" != "." ]; do
        A_DIR=$(dirname "$A_DIR")
    done
    if [ ! -w "$A_DIR" ]; then
        echo "FATAL PREFLIGHT ERROR: Ancestor direktori target restore tidak writable: $A_DIR"
        exit 1
    fi
done < "$PRE_DEPLOY_MANIFEST"

echo "-> Preflight lolos 100%! Seluruh target terbukti writable dan valid."

# -----------------------------------------------------------------------------
# 7. PEMBERSIHAN FILE BARU HASIL DEPLOYMENT (ORPHAN CLEANUP TERBATAS)
# -----------------------------------------------------------------------------
echo ""
echo "5. MENGIDENTIFIKASI & MENGHAPUS FILE BARU HASIL DEPLOYMENT..."

NEW_FILES_REMOVED=0

PRE_EXISTING_DIRS=$(mktemp)
(cd "$CORE_PATH" && find . -type d) > "$PRE_EXISTING_DIRS" || true
(cd "$PUBLIC_PATH" && find . -type d | sed 's|^./|public_html/|') >> "$PRE_EXISTING_DIRS" || true

while IFS= read -r file; do
    [ -z "$file" ] && continue

    if ! grep -Fxq "$file" "$PRE_DEPLOY_MANIFEST"; then
        if [[ "$file" == public_html/* ]]; then
            REL_PATH="${file#public_html/}"
            TARGET_FILE="${PUBLIC_PATH}/${REL_PATH}"
            BASE_BOUND="${PUBLIC_PATH}"
            ALLOWED_CANON="$PUB_CANON_BASE"
        else
            TARGET_FILE="${CORE_PATH}/${file}"
            BASE_BOUND="${CORE_PATH}"
            ALLOWED_CANON="$CORE_CANON_BASE"
        fi

        if [[ "$TARGET_FILE" == *"/.env"* ]] || [[ "$TARGET_FILE" == *"/vendor/"* ]] || [[ "$TARGET_FILE" == *"/storage/"* ]]; then
            echo " [PROTECT] File terproteksi dilewati: $TARGET_FILE"
            continue
        fi

        verify_canonical_containment "$TARGET_FILE" "$ALLOWED_CANON"

        if [ -f "$TARGET_FILE" ] || [ -L "$TARGET_FILE" ]; then
            /bin/rm -f "$TARGET_FILE"
            echo " [REMOVED NEW FILE] $TARGET_FILE"
            NEW_FILES_REMOVED=$((NEW_FILES_REMOVED + 1))

            CUR_DIR=$(dirname "$TARGET_FILE")
            while [ "$CUR_DIR" != "$BASE_BOUND" ] && [ "$CUR_DIR" != "/" ] && [ "$CUR_DIR" != "." ]; do
                REL_DIR="${CUR_DIR#$BASE_BOUND/}"
                if [[ "$BASE_BOUND" == "$PUBLIC_PATH" ]]; then
                    CHECK_DIR="public_html/${REL_DIR}"
                else
                    CHECK_DIR="./${REL_DIR}"
                fi

                if grep -Fxq "$CHECK_DIR" "$PRE_EXISTING_DIRS"; then
                    break
                fi

                verify_canonical_containment "$CUR_DIR" "$ALLOWED_CANON" || break

                if [ -d "$CUR_DIR" ] && [ -z "$(ls -A "$CUR_DIR" 2>/dev/null)" ]; then
                    /bin/rmdir "$CUR_DIR" 2>/dev/null || break
                    echo " [REMOVED NEW EMPTY DIR] $CUR_DIR"
                    CUR_DIR=$(dirname "$CUR_DIR")
                else
                    break
                fi
            done
        fi
    fi
done < "$INCOMING_MANIFEST"
rm -f "$PRE_EXISTING_DIRS"

echo "-> Total file baru hasil deployment yang dibersihkan: ${NEW_FILES_REMOVED}"

# -----------------------------------------------------------------------------
# 8. MEMULIHKAN SELURUH KOMPONEN CORE DARI SNAPSHOT
# -----------------------------------------------------------------------------
echo ""
echo "6. MEMULIHKAN SELURUH KOMPONEN CORE DARI SNAPSHOT..."

for dir in "app" "bootstrap" "config" "database" "resources" "routes" "lang"; do
    if [ -d "${TARGET_SNAPSHOT}/core/${dir}" ]; then
        verify_canonical_containment "${CORE_PATH}/${dir}" "$CORE_CANON_BASE" || exit 1
        /bin/mkdir -p "${CORE_PATH}/${dir}"
        /bin/cp -R "${TARGET_SNAPSHOT}/core/${dir}/." "${CORE_PATH}/${dir}/"
        echo " -> core/${dir}/ dipulihkan"
    fi
done

for core_file in "artisan" "composer.json" "composer.lock" "package.json" "package-lock.json"; do
    if [ -f "${TARGET_SNAPSHOT}/core/${core_file}" ]; then
        verify_canonical_containment "${CORE_PATH}/${core_file}" "$CORE_CANON_BASE" || exit 1
        /bin/cp "${TARGET_SNAPSHOT}/core/${core_file}" "${CORE_PATH}/${core_file}"
        echo " -> core/${core_file} dipulihkan"
    fi
done

# -----------------------------------------------------------------------------
# 9. MEMULIHKAN SELURUH KOMPONEN ZONA PUBLIK DARI SNAPSHOT
# -----------------------------------------------------------------------------
echo ""
echo "7. MEMULIHKAN SELURUH KOMPONEN ZONA PUBLIK DARI SNAPSHOT..."

for dir in "assets" "build"; do
    if [ -d "${TARGET_SNAPSHOT}/public/${dir}" ]; then
        verify_canonical_containment "${PUBLIC_PATH}/${dir}" "$PUB_CANON_BASE" || exit 1
        /bin/mkdir -p "${PUBLIC_PATH}/${dir}"
        /bin/cp -R "${TARGET_SNAPSHOT}/public/${dir}/." "${PUBLIC_PATH}/${dir}/"
        echo " -> public_html/${dir}/ dipulihkan"
    fi
done

for pub_file in "favicon.ico" "robots.txt" ".htaccess" "index.php"; do
    if [ -f "${TARGET_SNAPSHOT}/public/${pub_file}" ]; then
        verify_canonical_containment "${PUBLIC_PATH}/${pub_file}" "$PUB_CANON_BASE" || exit 1
        /bin/cp "${TARGET_SNAPSHOT}/public/${pub_file}" "${PUBLIC_PATH}/${pub_file}"
        echo " -> public_html/${pub_file} dipulihkan"
    fi
done

# -----------------------------------------------------------------------------
# 10. PEMBERSIHAN CACHE BLADE (OPSIONAL VIA FLAG --clear-views)
# -----------------------------------------------------------------------------
echo ""
if [ "$CLEAR_VIEWS_FLAG" -eq 1 ]; then
    echo "8. MEMBERSIHKAN CACHE VIEW BLADE (FLAG --clear-views AKTIF)..."
    /bin/rm -f "${CORE_PATH}/storage/framework/views/"*.php 2>/dev/null || true
    echo " -> Cache Blade dibersihkan sesuai instruksi operator."
else
    echo "8. STATUS STORAGE: 100% TIDAK DISENTUH (Cache Blade dipertahankan)."
    echo "   (Gunakan argumen --clear-views jika ingin membersihkan storage/framework/views/*.php secara eksplisit)."
fi

echo ""
echo "============================================================================="
echo " STATUS: ROLLBACK SELESAI & TERVERIFIKASI!"
echo " Seluruh file yang diubah telah dipulihkan ke versi snapshot."
echo " File baru hasil deploy telah dibersihkan (${NEW_FILES_REMOVED} file)."
echo " Catatan: .env, vendor, dan storage/app/public tetap utuh tanpa terpengaruh."
echo "============================================================================="
exit 0
