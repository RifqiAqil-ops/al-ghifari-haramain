-- =============================================================================
-- AL GHIFARI HARAMAIN - TAHAP 3: VERIFIKASI PASCA-RESET & KEPUTUSAN TRANSAKSI
-- Target Server   : cPanel Terminal / SSH (MySQL CLI Interaktif)
-- Target Database : xonkgvsz_alghifari
-- Kredensial DB   : User xonkgvsz_aghuser
-- Sifat Kueri     : VERIFIKASI VISUAL DI DALAM SESI TRANSAKSI AKTIF YANG SAMA
-- Status Dokumen  : DRAF PENINJAUAN TEKNIS (JANGAN DIJALANKAN TANPA PERSETUJUAN EKSPLISIT)
-- =============================================================================
--
-- PANDUAN PENTING:
-- Kueri ini WAJIB dijalankan pada SESI KONEKSI MYSQL CLI YANG SAMA PERSIS dengan
-- Tahap 2, sesaat setelah Tahap 2 selesai dan SEBELUM mengetikkan COMMIT atau ROLLBACK.
-- =============================================================================

USE `xonkgvsz_alghifari`;

-- -----------------------------------------------------------------------------
-- 1. VERIFIKASI 19 TABEL OPERASIONAL BERSIH (TARGET: 0 RECORD)
-- -----------------------------------------------------------------------------
SELECT
    '1. Pembersihan 19 Tabel Operasional' AS `indikator_pemeriksaan`,
    CAST((
        (SELECT COUNT(*) FROM `accommodations`) +
        (SELECT COUNT(*) FROM `activity_logs`) +
        (SELECT COUNT(*) FROM `departures`) +
        (SELECT COUNT(*) FROM `departure_checklists`) +
        (SELECT COUNT(*) FROM `expenses`) +
        (SELECT COUNT(*) FROM `flights`) +
        (SELECT COUNT(*) FROM `invoices`) +
        (SELECT COUNT(*) FROM `itineraries`) +
        (SELECT COUNT(*) FROM `itinerary_items`) +
        (SELECT COUNT(*) FROM `jamaahs`) +
        (SELECT COUNT(*) FROM `jamaah_contacts`) +
        (SELECT COUNT(*) FROM `jamaah_documents`) +
        (SELECT COUNT(*) FROM `notifications`) +
        (SELECT COUNT(*) FROM `packages`) +
        (SELECT COUNT(*) FROM `package_facilities`) +
        (SELECT COUNT(*) FROM `payments`) +
        (SELECT COUNT(*) FROM `refunds`) +
        (SELECT COUNT(*) FROM `registrations`) +
        (SELECT COUNT(*) FROM `whatsapp_messages`)
    ) AS CHAR) AS `kondisi_aktual`,
    '0 Record (Bersih Total)' AS `kondisi_ekspektasi`,
    CASE WHEN (
        (SELECT COUNT(*) FROM `accommodations`) +
        (SELECT COUNT(*) FROM `activity_logs`) +
        (SELECT COUNT(*) FROM `departures`) +
        (SELECT COUNT(*) FROM `departure_checklists`) +
        (SELECT COUNT(*) FROM `expenses`) +
        (SELECT COUNT(*) FROM `flights`) +
        (SELECT COUNT(*) FROM `invoices`) +
        (SELECT COUNT(*) FROM `itineraries`) +
        (SELECT COUNT(*) FROM `itinerary_items`) +
        (SELECT COUNT(*) FROM `jamaahs`) +
        (SELECT COUNT(*) FROM `jamaah_contacts`) +
        (SELECT COUNT(*) FROM `jamaah_documents`) +
        (SELECT COUNT(*) FROM `notifications`) +
        (SELECT COUNT(*) FROM `packages`) +
        (SELECT COUNT(*) FROM `package_facilities`) +
        (SELECT COUNT(*) FROM `payments`) +
        (SELECT COUNT(*) FROM `refunds`) +
        (SELECT COUNT(*) FROM `registrations`) +
        (SELECT COUNT(*) FROM `whatsapp_messages`)
    ) = 0 THEN 'LOLOS (BERSIH 100%)' ELSE 'GAGAL' END AS `status_kelulusan`

UNION ALL

-- -----------------------------------------------------------------------------
-- 2. VERIFIKASI TOTAL PENGGUNA (TARGET: TEPAT 6 USER RESMI)
-- -----------------------------------------------------------------------------
SELECT
    '2. Total Pengguna Aktif Pasca-Reset',
    CAST(COUNT(*) AS CHAR),
    '6 Akun Resmi',
    CASE WHEN COUNT(*) = 6 THEN 'LOLOS' ELSE 'GAGAL' END
FROM `users`

UNION ALL

-- -----------------------------------------------------------------------------
-- 3. VERIFIKASI AKUN PENGUJIAN HILANG TOTAL (TARGET: 0 RECORD)
-- -----------------------------------------------------------------------------
SELECT
    '3. Status Akun test@example.com',
    CAST(COUNT(*) AS CHAR),
    '0 Record (Terhapus Sempurna)',
    CASE WHEN COUNT(*) = 0 THEN 'LOLOS (HAPUS BERHASIL)' ELSE 'GAGAL' END
FROM `users`
WHERE `email` = 'test@example.com'

UNION ALL

-- -----------------------------------------------------------------------------
-- 4. VERIFIKASI ENAM AKUN RESMI & ROLE (TANPA ROLE TAMBAHAN)
-- -----------------------------------------------------------------------------
SELECT
    '4. Verifikasi 6 Akun Resmi & Role Tepat (Tanpa Role Ekstra)',
    CAST(COUNT(*) AS CHAR),
    '6 Akun (Masing-masing Tepat 1 Role)',
    CASE WHEN COUNT(*) = 6 THEN 'LOLOS' ELSE 'GAGAL' END
FROM (
    SELECT u.`id`, u.`email`, COUNT(mhr.`role_id`) AS `role_count`
    FROM `users` u
    JOIN `model_has_roles` mhr ON mhr.`model_id` = u.`id` AND mhr.`model_type` = 'App\\Models\\User'
    JOIN `roles` r ON r.`id` = mhr.`role_id`
    WHERE (u.`email` = 'superadmin@alghifari.com' AND r.`name` = 'Super Admin')
       OR (u.`email` = 'admin@alghifari.com'      AND r.`name` = 'Admin')
       OR (u.`email` = 'staff@alghifari.com'      AND r.`name` = 'Staff')
       OR (u.`email` = 'finance@alghifari.com'    AND r.`name` = 'Finance')
       OR (u.`email` = 'manager@alghifari.com'    AND r.`name` = 'Manager')
       OR (u.`email` = 'rifqiaqil822@gmail.com'   AND r.`name` = 'Owner')
    GROUP BY u.`id`, u.`email`
    HAVING `role_count` = 1
) AS `valid_users_post`

UNION ALL

-- -----------------------------------------------------------------------------
-- 5. VERIFIKASI ORPHAN RBAC (TARGET: 0 RECORD DI MHR & MHP)
-- -----------------------------------------------------------------------------
SELECT
    '5. Orphan Records RBAC (mhr & mhp)',
    CAST((
        (SELECT COUNT(*) FROM `model_has_roles` mhr LEFT JOIN `users` u ON u.`id` = mhr.`model_id` AND mhr.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL) +
        (SELECT COUNT(*) FROM `model_has_permissions` mhp LEFT JOIN `users` u ON u.`id` = mhp.`model_id` AND mhp.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL)
    ) AS CHAR),
    '0 Orphan Record',
    CASE WHEN (
        (SELECT COUNT(*) FROM `model_has_roles` mhr LEFT JOIN `users` u ON u.`id` = mhr.`model_id` AND mhr.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL) +
        (SELECT COUNT(*) FROM `model_has_permissions` mhp LEFT JOIN `users` u ON u.`id` = mhp.`model_id` AND mhp.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL)
    ) = 0 THEN 'LOLOS' ELSE 'GAGAL' END

UNION ALL

-- -----------------------------------------------------------------------------
-- 6. VERIFIKASI PEMBERSIHAN TRANSIENT SESI & CACHE (TARGET: 0 RECORD)
-- -----------------------------------------------------------------------------
SELECT
    '6. Pembersihan Sesi & Cache (sessions, cache)',
    CAST((
        (SELECT COUNT(*) FROM `sessions`) +
        (SELECT COUNT(*) FROM `cache`) +
        (SELECT COUNT(*) FROM `cache_locks`) +
        (SELECT COUNT(*) FROM `password_reset_tokens`)
    ) AS CHAR),
    '0 Record',
    CASE WHEN (
        (SELECT COUNT(*) FROM `sessions`) +
        (SELECT COUNT(*) FROM `cache`) +
        (SELECT COUNT(*) FROM `cache_locks`) +
        (SELECT COUNT(*) FROM `password_reset_tokens`)
    ) = 0 THEN 'LOLOS' ELSE 'GAGAL' END;

-- -----------------------------------------------------------------------------
-- 7. AUDIT SATU PER SATU 6 AKUN RESMI YANG BERTAHAN (RINCIAN DETAIL)
-- -----------------------------------------------------------------------------
SELECT
    u.`id` AS `user_id`,
    u.`name` AS `nama_lengkap`,
    u.`email` AS `email_resmi`,
    COUNT(mhr.`role_id`) AS `jumlah_role`,
    GROUP_CONCAT(r.`name` ORDER BY r.`name` SEPARATOR ', ') AS `role_terpasang`,
    CASE
        WHEN u.`email` = 'superadmin@alghifari.com' AND GROUP_CONCAT(r.`name`) = 'Super Admin' THEN 'VALID: 1 Role (Super Admin)'
        WHEN u.`email` = 'admin@alghifari.com'      AND GROUP_CONCAT(r.`name`) = 'Admin'       THEN 'VALID: 1 Role (Admin)'
        WHEN u.`email` = 'staff@alghifari.com'      AND GROUP_CONCAT(r.`name`) = 'Staff'       THEN 'VALID: 1 Role (Staff)'
        WHEN u.`email` = 'finance@alghifari.com'    AND GROUP_CONCAT(r.`name`) = 'Finance'     THEN 'VALID: 1 Role (Finance)'
        WHEN u.`email` = 'manager@alghifari.com'    AND GROUP_CONCAT(r.`name`) = 'Manager'     THEN 'VALID: 1 Role (Manager)'
        WHEN u.`email` = 'rifqiaqil822@gmail.com'   AND GROUP_CONCAT(r.`name`) = 'Owner'       THEN 'VALID: 1 Role (Owner)'
        ELSE 'ANOMALI ROLE TERDETEKSI'
    END AS `evaluasi_role_pasca_reset`
FROM `users` u
JOIN `model_has_roles` mhr ON mhr.`model_id` = u.`id` AND mhr.`model_type` = 'App\\Models\\User'
JOIN `roles` r ON r.`id` = mhr.`role_id`
GROUP BY u.`id`, u.`name`, u.`email`
ORDER BY u.`id` ASC;

-- -----------------------------------------------------------------------------
-- 8. TABEL KOMPARASI DATA MASTER, RBAC, QUEUE & MIGRATIONS (SEBELUM VS SESUDAH)
-- Menjamin secara side-by-side bahwa tidak ada satu baris pun data konfigurasi yang berkurang.
-- -----------------------------------------------------------------------------
SELECT
    'bank_accounts' AS `tabel_master`,
    @pre_bank_accounts AS `jumlah_sebelum`,
    (SELECT COUNT(*) FROM `bank_accounts`) AS `jumlah_sesudah`,
    CASE WHEN @pre_bank_accounts = (SELECT COUNT(*) FROM `bank_accounts`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END AS `status_integritas`
UNION ALL SELECT 'company_settings', @pre_company_settings, (SELECT COUNT(*) FROM `company_settings`), CASE WHEN @pre_company_settings = (SELECT COUNT(*) FROM `company_settings`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'transaction_categories', @pre_transaction_categories, (SELECT COUNT(*) FROM `transaction_categories`), CASE WHEN @pre_transaction_categories = (SELECT COUNT(*) FROM `transaction_categories`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'whatsapp_templates', @pre_whatsapp_templates, (SELECT COUNT(*) FROM `whatsapp_templates`), CASE WHEN @pre_whatsapp_templates = (SELECT COUNT(*) FROM `whatsapp_templates`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'roles', @pre_roles, (SELECT COUNT(*) FROM `roles`), CASE WHEN @pre_roles = (SELECT COUNT(*) FROM `roles`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'permissions', @pre_permissions, (SELECT COUNT(*) FROM `permissions`), CASE WHEN @pre_permissions = (SELECT COUNT(*) FROM `permissions`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'role_has_permissions', @pre_role_has_permissions, (SELECT COUNT(*) FROM `role_has_permissions`), CASE WHEN @pre_role_has_permissions = (SELECT COUNT(*) FROM `role_has_permissions`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'jobs', @pre_jobs, (SELECT COUNT(*) FROM `jobs`), CASE WHEN @pre_jobs = (SELECT COUNT(*) FROM `jobs`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'job_batches', @pre_job_batches, (SELECT COUNT(*) FROM `job_batches`), CASE WHEN @pre_job_batches = (SELECT COUNT(*) FROM `job_batches`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'failed_jobs', @pre_failed_jobs, (SELECT COUNT(*) FROM `failed_jobs`), CASE WHEN @pre_failed_jobs = (SELECT COUNT(*) FROM `failed_jobs`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END
UNION ALL SELECT 'migrations', @pre_migrations, (SELECT COUNT(*) FROM `migrations`), CASE WHEN @pre_migrations = (SELECT COUNT(*) FROM `migrations`) THEN 'IDENTIK (UTUH)' ELSE 'ANOMALI (BERUBAH)' END;

-- =============================================================================
-- 9. PANDUAN PENGAMBILAN KEPUTUSAN FINAL OLEH OPERATOR:
-- =============================================================================
-- Periksa seluruh hasil kueri di atas:
--
-- JIKA SELURUH HASIL SESUAI DAN SEMPURNA (100% LOLOS & IDENTIK UTUH):
-- Ketikkan perintah berikut di terminal lalu tekan Enter:
-- COMMIT;
--
-- JIKA TERDAPAT KEJANGGALAN, ANOMALI, ERROR DML, ATAU INGIN MEMBATALKAN:
-- Ketikkan perintah berikut di terminal lalu tekan Enter:
-- ROLLBACK;
-- =============================================================================
