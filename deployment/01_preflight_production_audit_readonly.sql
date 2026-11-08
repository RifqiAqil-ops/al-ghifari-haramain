-- =============================================================================
-- AL GHIFARI HARAMAIN - TAHAP 1: PREFLIGHT AUDIT READ-ONLY PRODUCTION
-- Target Server   : AnymHost cPanel (phpMyAdmin atau MySQL CLI)
-- Target Database : xonkgvsz_alghifari
-- Kredensial DB   : User xonkgvsz_aghuser
-- Sifat Kueri     : 100% READ-ONLY (SELECT MURNI - TANPA MODIFIKASI DATA APAPUN)
-- Status Dokumen  : DRAF AUDIT PRA-SYARAT (AMAN DIJALANKAN DI PHPMYADMIN / CLI)
-- =============================================================================
--
-- PANDUAN EKSEKUSI:
-- 1. Kueri ini aman dijalankan di phpMyAdmin atau MySQL CLI karena 100% READ-ONLY.
-- 2. Seluruh 10 indikator checklist pada Bagian 2 wajib berstatus 'LOLOS'.
-- 3. Bagian 3 secara spesifik mengaudit 6 akun resmi untuk mendeteksi apakah ada
--    akun yang hilang/belum terdaftar, role yang salah, atau role tambahan.
-- 4. Bagian 4 memeriksa seluruh record aktual di tabel users (termasuk akun uji).
-- 5. Bagian 7 menyajikan daftar lengkap 32 Foreign Key aktif production untuk
--    memverifikasi tabel anak, kolom anak, tabel induk, kolom induk, dan constraint.
-- =============================================================================

USE `xonkgvsz_alghifari`;

-- -----------------------------------------------------------------------------
-- 1. IDENTIFIKASI LINGKUNGAN DATABASE AKTIF
-- -----------------------------------------------------------------------------
SELECT 
    DATABASE() AS `active_database`,
    USER() AS `connected_user`,
    VERSION() AS `mysql_version`,
    NOW() AS `execution_time`,
    CASE 
        WHEN DATABASE() = 'xonkgvsz_alghifari' THEN 'VALID: Terhubung ke Database Production xonkgvsz_alghifari'
        ELSE 'FATAL ERROR: BUKAN Database Production xonkgvsz_alghifari!'
    END AS `verifikasi_database`;

-- -----------------------------------------------------------------------------
-- 2. MATRIKS CHECKLIST 10 PRA-SYARAT INTEGRITAS (WAJIB LOLOS 10/10)
-- -----------------------------------------------------------------------------
SELECT 
    '1. Verifikasi Nama Database' AS `indikator_pemeriksaan`,
    DATABASE() AS `kondisi_aktual`,
    'xonkgvsz_alghifari' AS `kondisi_ekspektasi`,
    CASE WHEN DATABASE() = 'xonkgvsz_alghifari' THEN 'LOLOS' ELSE 'GAGAL' END AS `status_kelulusan`

UNION ALL

SELECT 
    '2. Enam Akun Resmi & Role Tepat (Tanpa Role Tambahan)',
    CAST(COUNT(*) AS CHAR),
    '6 Akun (Masing-masing Tepat 1 Role Sah)',
    CASE WHEN COUNT(*) = 6 THEN 'LOLOS' ELSE 'GAGAL' END
FROM (
    SELECT exp.expected_email
    FROM (
        SELECT 'superadmin@alghifari.com' AS expected_email, 'Super Admin' AS expected_role
        UNION ALL SELECT 'admin@alghifari.com', 'Admin'
        UNION ALL SELECT 'staff@alghifari.com', 'Staff'
        UNION ALL SELECT 'finance@alghifari.com', 'Finance'
        UNION ALL SELECT 'manager@alghifari.com', 'Manager'
        UNION ALL SELECT 'rifqiaqil822@gmail.com', 'Owner'
    ) AS exp
    JOIN `users` u ON u.`email` = exp.expected_email
    JOIN `model_has_roles` mhr ON mhr.`model_id` = u.`id` AND mhr.`model_type` = 'App\\Models\\User'
    JOIN `roles` r ON r.`id` = mhr.`role_id`
    GROUP BY exp.expected_email, exp.expected_role
    HAVING COUNT(mhr.`role_id`) = 1 AND MAX(r.`name`) = exp.expected_role
) AS `valid_official_users`

UNION ALL

SELECT 
    '3. Akun Pengujian Tunggal (Target Hapus, 0 Role)',
    CAST(COUNT(*) AS CHAR),
    '1 Akun (test@example.com tanpa role)',
    CASE WHEN COUNT(*) = 1 THEN 'LOLOS' ELSE 'GAGAL' END
FROM `users` u
LEFT JOIN `model_has_roles` mhr ON mhr.`model_id` = u.`id` AND mhr.`model_type` = 'App\\Models\\User'
WHERE u.`email` = 'test@example.com' AND mhr.`role_id` IS NULL

UNION ALL

SELECT 
    '4. Total Akun Users Sebelum Reset',
    CAST(COUNT(*) AS CHAR),
    '7 Akun (6 Resmi + 1 Demo)',
    CASE WHEN COUNT(*) = 7 THEN 'LOLOS' ELSE 'GAGAL' END
FROM `users`

UNION ALL

SELECT 
    '5. Foreign Key Constraints Production (Distinct Constraints)',
    CAST(COUNT(DISTINCT CONSTRAINT_NAME) AS CHAR),
    '32 Constraints Aktif',
    CASE WHEN COUNT(DISTINCT CONSTRAINT_NAME) = 32 THEN 'LOLOS' ELSE 'GAGAL' END
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL

UNION ALL

SELECT 
    '6. Rekening Bank Resmi (bank_accounts)',
    CAST(COUNT(*) AS CHAR),
    'Minimal 3 Rekening',
    CASE WHEN COUNT(*) >= 3 THEN 'LOLOS' ELSE 'GAGAL' END
FROM `bank_accounts`

UNION ALL

SELECT 
    '7. Pengaturan Perusahaan (company_settings)',
    CAST(COUNT(*) AS CHAR),
    'Minimal 1 Profil',
    CASE WHEN COUNT(*) >= 1 THEN 'LOLOS' ELSE 'GAGAL' END
FROM `company_settings`

UNION ALL

SELECT 
    '8. Kategori Transaksi (transaction_categories)',
    CAST(COUNT(*) AS CHAR),
    'Minimal 9 Kategori',
    CASE WHEN COUNT(*) >= 9 THEN 'LOLOS' ELSE 'GAGAL' END
FROM `transaction_categories`

UNION ALL

SELECT 
    '9. Template WhatsApp Resmi (whatsapp_templates)',
    CAST(COUNT(*) AS CHAR),
    'Minimal 4 Template',
    CASE WHEN COUNT(*) >= 4 THEN 'LOLOS' ELSE 'GAGAL' END
FROM `whatsapp_templates`

UNION ALL

SELECT 
    '10. Integritas RBAC Tanpa Orphan (mhr & mhp)',
    CAST((
        (SELECT COUNT(*) FROM `model_has_roles` mhr LEFT JOIN `users` u ON u.`id` = mhr.`model_id` AND mhr.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL) +
        (SELECT COUNT(*) FROM `model_has_permissions` mhp LEFT JOIN `users` u ON u.`id` = mhp.`model_id` AND mhp.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL)
    ) AS CHAR),
    '0 Orphan Record',
    CASE WHEN (
        (SELECT COUNT(*) FROM `model_has_roles` mhr LEFT JOIN `users` u ON u.`id` = mhr.`model_id` AND mhr.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL) +
        (SELECT COUNT(*) FROM `model_has_permissions` mhp LEFT JOIN `users` u ON u.`id` = mhp.`model_id` AND mhp.`model_type` = 'App\\Models\\User' WHERE u.`id` IS NULL)
    ) = 0 THEN 'LOLOS' ELSE 'GAGAL' END;

-- -----------------------------------------------------------------------------
-- 3. AUDIT INDIVIDUAL 6 AKUN RESMI (DETEKSI AKUN HILANG, ROLE SALAH, & ROLE TAMBAHAN)
-- -----------------------------------------------------------------------------
SELECT 
    exp.`expected_email` AS `email_target_resmi`,
    exp.`expected_role` AS `role_ekspektasi`,
    u.`id` AS `user_id_aktual`,
    u.`name` AS `nama_aktual`,
    COUNT(mhr.`role_id`) AS `jumlah_role_aktual`,
    COALESCE(GROUP_CONCAT(r.`name` ORDER BY r.`name` SEPARATOR ', '), 'TIDAK ADA ROLE') AS `role_aktual`,
    CASE 
        WHEN u.`id` IS NULL THEN 
            'ERROR: AKUN HILANG (Email Belum Terdaftar di Tabel users)'
        WHEN COUNT(mhr.`role_id`) = 0 THEN 
            'ERROR: ROLE KOSONG (User Terdaftar Tetapi Belum Diberi Role)'
        WHEN COUNT(mhr.`role_id`) > 1 THEN 
            CONCAT('ERROR: ROLE TAMBAHAN (Terdeteksi ', COUNT(mhr.`role_id`), ' Role: ', GROUP_CONCAT(r.`name` SEPARATOR ', '), ')')
        WHEN MAX(r.`name`) <> exp.`expected_role` THEN 
            CONCAT('ERROR: ROLE SALAH (Aktual: ', MAX(r.`name`), ', Ekspektasi: ', exp.`expected_role`, ')')
        ELSE 
            CONCAT('LOLOS: VALID (Tepat 1 Role: ', exp.`expected_role`, ')')
    END AS `status_evaluasi`
FROM (
    SELECT 'superadmin@alghifari.com' AS expected_email, 'Super Admin' AS expected_role
    UNION ALL SELECT 'admin@alghifari.com', 'Admin'
    UNION ALL SELECT 'staff@alghifari.com', 'Staff'
    UNION ALL SELECT 'finance@alghifari.com', 'Finance'
    UNION ALL SELECT 'manager@alghifari.com', 'Manager'
    UNION ALL SELECT 'rifqiaqil822@gmail.com', 'Owner'
) AS exp
LEFT JOIN `users` u ON u.`email` = exp.expected_email
LEFT JOIN `model_has_roles` mhr ON mhr.`model_id` = u.`id` AND mhr.`model_type` = 'App\\Models\\User'
LEFT JOIN `roles` r ON r.`id` = mhr.`role_id`
GROUP BY exp.`expected_email`, exp.`expected_role`, u.`id`, u.`name`
ORDER BY FIELD(exp.`expected_email`, 
    'superadmin@alghifari.com', 
    'admin@alghifari.com', 
    'staff@alghifari.com', 
    'finance@alghifari.com', 
    'manager@alghifari.com', 
    'rifqiaqil822@gmail.com'
);

-- -----------------------------------------------------------------------------
-- 4. DAFTAR LENGKAP SELURUH PENGGUNA AKTUAL DI TABEL USERS (AUDIT EKSISTENSI & TEST ACCOUNT)
-- -----------------------------------------------------------------------------
SELECT 
    u.`id` AS `user_id`,
    u.`name` AS `nama_lengkap`,
    u.`email` AS `email_terdaftar`,
    COUNT(mhr.`role_id`) AS `jumlah_role`,
    COALESCE(GROUP_CONCAT(r.`name` ORDER BY r.`name` SEPARATOR ', '), 'TIDAK ADA ROLE') AS `daftar_role`,
    CASE 
        WHEN u.`email` = 'superadmin@alghifari.com' AND GROUP_CONCAT(r.`name`) = 'Super Admin' THEN 'RESMI VALID (Super Admin)'
        WHEN u.`email` = 'admin@alghifari.com'      AND GROUP_CONCAT(r.`name`) = 'Admin'       THEN 'RESMI VALID (Admin)'
        WHEN u.`email` = 'staff@alghifari.com'      AND GROUP_CONCAT(r.`name`) = 'Staff'       THEN 'RESMI VALID (Staff)'
        WHEN u.`email` = 'finance@alghifari.com'    AND GROUP_CONCAT(r.`name`) = 'Finance'     THEN 'RESMI VALID (Finance)'
        WHEN u.`email` = 'manager@alghifari.com'    AND GROUP_CONCAT(r.`name`) = 'Manager'     THEN 'RESMI VALID (Manager)'
        WHEN u.`email` = 'rifqiaqil822@gmail.com'   AND GROUP_CONCAT(r.`name`) = 'Owner'       THEN 'RESMI VALID (Owner)'
        WHEN u.`email` = 'test@example.com'        AND COUNT(mhr.`role_id`) = 0                THEN 'TARGET HAPUS VALID (Akun Uji Tanpa Role)'
        ELSE 'PERIKSA: Akun Tidak Dikenal / Role Tidak Sesuai Spesifikasi'
    END AS `klasifikasi_akun`
FROM `users` u
LEFT JOIN `model_has_roles` mhr ON mhr.`model_id` = u.`id` AND mhr.`model_type` = 'App\\Models\\User'
LEFT JOIN `roles` r ON r.`id` = mhr.`role_id`
GROUP BY u.`id`, u.`name`, u.`email`
ORDER BY u.`id` ASC;

-- -----------------------------------------------------------------------------
-- 5. INVENTARISASI DATA 19 TABEL OPERASIONAL YANG AKAN DIHAPUS
-- -----------------------------------------------------------------------------
SELECT 'accommodations' AS `tabel_operasional`, COUNT(*) AS `record_akan_dihapus` FROM `accommodations`
UNION ALL SELECT 'activity_logs', COUNT(*) FROM `activity_logs`
UNION ALL SELECT 'departures', COUNT(*) FROM `departures`
UNION ALL SELECT 'departure_checklists', COUNT(*) FROM `departure_checklists`
UNION ALL SELECT 'expenses', COUNT(*) FROM `expenses`
UNION ALL SELECT 'flights', COUNT(*) FROM `flights`
UNION ALL SELECT 'invoices', COUNT(*) FROM `invoices`
UNION ALL SELECT 'itineraries', COUNT(*) FROM `itineraries`
UNION ALL SELECT 'itinerary_items', COUNT(*) FROM `itinerary_items`
UNION ALL SELECT 'jamaahs', COUNT(*) FROM `jamaahs`
UNION ALL SELECT 'jamaah_contacts', COUNT(*) FROM `jamaah_contacts`
UNION ALL SELECT 'jamaah_documents', COUNT(*) FROM `jamaah_documents`
UNION ALL SELECT 'notifications', COUNT(*) FROM `notifications`
UNION ALL SELECT 'packages', COUNT(*) FROM `packages`
UNION ALL SELECT 'package_facilities', COUNT(*) FROM `package_facilities`
UNION ALL SELECT 'payments', COUNT(*) FROM `payments`
UNION ALL SELECT 'refunds', COUNT(*) FROM `refunds`
UNION ALL SELECT 'registrations', COUNT(*) FROM `registrations`
UNION ALL SELECT 'whatsapp_messages', COUNT(*) FROM `whatsapp_messages`
ORDER BY `tabel_operasional` ASC;

-- -----------------------------------------------------------------------------
-- 6. VERIFIKASI DATA MASTER & ANTREAN YANG DILARANG DISENTUH
-- -----------------------------------------------------------------------------
SELECT 
    (SELECT COUNT(*) FROM `bank_accounts`) AS `rekening_bank_retained`,
    (SELECT COUNT(*) FROM `company_settings`) AS `company_settings_retained`,
    (SELECT COUNT(*) FROM `transaction_categories`) AS `categories_retained`,
    (SELECT COUNT(*) FROM `whatsapp_templates`) AS `wa_templates_retained`,
    (SELECT COUNT(*) FROM `roles`) AS `roles_retained`,
    (SELECT COUNT(*) FROM `permissions`) AS `permissions_retained`,
    (SELECT COUNT(*) FROM `role_has_permissions`) AS `role_permissions_retained`,
    (SELECT COUNT(*) FROM `jobs`) AS `jobs_queue_retained`,
    (SELECT COUNT(*) FROM `job_batches`) AS `job_batches_retained`,
    (SELECT COUNT(*) FROM `failed_jobs`) AS `failed_jobs_retained`,
    (SELECT COUNT(*) FROM `migrations`) AS `migrations_retained`;

-- -----------------------------------------------------------------------------
-- 7. DAFTAR LENGKAP 32 FOREIGN KEY AKTIF DATABASE PRODUCTION
-- -----------------------------------------------------------------------------
SELECT 
    TABLE_NAME AS `tabel_anak`,
    COLUMN_NAME AS `kolom_anak`,
    REFERENCED_TABLE_NAME AS `tabel_induk`,
    REFERENCED_COLUMN_NAME AS `kolom_induk`,
    CONSTRAINT_NAME AS `nama_constraint`
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME ASC, CONSTRAINT_NAME ASC, ORDINAL_POSITION ASC;
