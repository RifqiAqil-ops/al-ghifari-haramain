-- =============================================================================
-- AL GHIFARI HARAMAIN - TAHAP 2: EKSEKUSI RESET DATA OPERASIONAL (INTERAKTIF CLI)
-- Target Server   : cPanel Terminal / SSH (MySQL CLI Interaktif)
-- Target Database : xonkgvsz_alghifari
-- Kredensial DB   : User xonkgvsz_aghuser
-- Perintah Akses  : mysql -u xonkgvsz_aghuser -p xonkgvsz_alghifari
-- Sifat Kueri     : DML BERTRANSAKSI TANPA COMMIT OTOMATIS (ZERO DDL, ZERO STORED PROCEDURE)
-- Status Dokumen  : DRAF PENINJAUAN TEKNIS (JANGAN DIJALANKAN TANPA PERSETUJUAN EKSPLISIT)
-- =============================================================================
--
-- ATURAN KESELAMATAN & ERROR HANDLING:
-- 1. File ini HANYA boleh dijalankan melalui cPanel Terminal / MySQL CLI Interaktif.
-- 2. DILARANG MENJALANKAN FILE INI MELALUI PHPMYADMIN.
-- 3. File ini SENGAJA TIDAK MEMUAT PERINTAH 'COMMIT;'.
-- 4. ATURAN PENANGANAN ERROR MUTLAK:
--    Ketika mem-paste kueri ini, perhatikan dengan teliti keluaran terminal.
--    JIKA TERDAPAT SATU SAJA ERROR (misal ERROR 1451, ERROR 1064, dll):
--    - JANGAN mengasumsikan transaksi otomatis berhenti.
--    - DILARANG melakukan COMMIT.
--    - SEGERA KETIK: ROLLBACK; lalu tekan Enter.
-- 5. Jika seluruh perintah berhasil tanpa error sama sekali, lanjutkan langsung ke Tahap 3
--    (03_post_reset_verification_interactive.sql) DALAM SESI KONEKSI YANG SAMA PERSIS.
-- =============================================================================

USE `xonkgvsz_alghifari`;

-- Pastikan Foreign Key Checks AKTIF 100% (SET FOREIGN_KEY_CHECKS = 1)
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- 1. REKAM BASELINE DATA MASTER & QUEUE SEBELUM DML (UNTUK KOMPARASI TAHAP 3)
-- =============================================================================
SELECT COUNT(*) INTO @pre_bank_accounts FROM `bank_accounts`;
SELECT COUNT(*) INTO @pre_company_settings FROM `company_settings`;
SELECT COUNT(*) INTO @pre_transaction_categories FROM `transaction_categories`;
SELECT COUNT(*) INTO @pre_whatsapp_templates FROM `whatsapp_templates`;
SELECT COUNT(*) INTO @pre_roles FROM `roles`;
SELECT COUNT(*) INTO @pre_permissions FROM `permissions`;
SELECT COUNT(*) INTO @pre_role_has_permissions FROM `role_has_permissions`;
SELECT COUNT(*) INTO @pre_jobs FROM `jobs`;
SELECT COUNT(*) INTO @pre_job_batches FROM `job_batches`;
SELECT COUNT(*) INTO @pre_failed_jobs FROM `failed_jobs`;
SELECT COUNT(*) INTO @pre_migrations FROM `migrations`;

-- =============================================================================
-- 2. MULAI TRANSAKSI ATOMIK INNODB
-- =============================================================================
START TRANSACTION;

-- =============================================================================
-- 3. PENGHAPUSAN TOPOLOGIS CHILD-TO-PARENT SESUAI 32 FOREIGN KEY PRODUCTION
-- Tidak ada TRUNCATE. Integritas referensial terjaga 100%.
-- =============================================================================

-- Tingkat 1: Daun Terluar (Log Aktivitas, Notifikasi, Pesan WhatsApp)
DELETE FROM `activity_logs`;
DELETE FROM `notifications`;
DELETE FROM `whatsapp_messages`;

-- Tingkat 2: Transaksi Keuangan (Payments & Refunds -> Invoices)
DELETE FROM `payments`;
DELETE FROM `refunds`;
DELETE FROM `invoices`;

-- Tingkat 3: Pendaftaran Transaksi Jamaah
DELETE FROM `registrations`;

-- Tingkat 4: Berkas & Kontak Darurat Jamaah -> Master Jamaah
DELETE FROM `jamaah_documents`;
DELETE FROM `jamaah_contacts`;
DELETE FROM `jamaahs`;

-- Tingkat 5: Rincian Jadwal & Logistik Keberangkatan
DELETE FROM `departure_checklists`;
DELETE FROM `itinerary_items`;
DELETE FROM `itineraries`;
DELETE FROM `accommodations`;
DELETE FROM `flights`;
DELETE FROM `expenses`;

-- Tingkat 6: Keberangkatan & Paket Perjalanan
DELETE FROM `departures`;
DELETE FROM `package_facilities`;
DELETE FROM `packages`;

-- Tingkat 7: Data Sesi & Cache Transient Sistem
DELETE FROM `sessions`;
DELETE FROM `cache_locks`;
DELETE FROM `cache`;
DELETE FROM `password_reset_tokens`;

-- Tingkat 8: Pembersihan Khusus Akun Pengujian (test@example.com)
-- Hapus relasi polymorphic RBAC milik akun test (jika ada)
DELETE FROM `model_has_roles`
WHERE `model_type` = 'App\\Models\\User'
  AND `model_id` IN (SELECT `id` FROM `users` WHERE `email` = 'test@example.com');

DELETE FROM `model_has_permissions`
WHERE `model_type` = 'App\\Models\\User'
  AND `model_id` IN (SELECT `id` FROM `users` WHERE `email` = 'test@example.com');

-- Hapus akun test dari tabel users
DELETE FROM `users`
WHERE `email` = 'test@example.com';

-- =============================================================================
-- 4. KONFIRMASI STATUS TRANSAKSI MASIH TERBUKA
-- =============================================================================
SELECT
    'TRANSAKSI DML SELESAI DIEKSEKUSI (STATUS: UNCOMMITTED)' AS `status_tahap_2`,
    'Seluruh DML berhasil diproses dalam transaksi InnoDB. DATA BELUM DI-COMMIT.' AS `kondisi`,
    'JIKA ADA ERROR DI ATAS: Ketik ROLLBACK; dan Enter. JIKA BERSIH: Lanjut ke Tahap 3.' AS `petunjuk_operator`;

-- =============================================================================
-- PENTING: JANGAN KELUAR DARI TERMINAL!
-- Transaksi saat ini sedang aktif di buffer InnoDB MariaDB.
-- Lanjutkan langsung ke Tahap 3 (03_post_reset_verification_interactive.sql)
-- pada sesi terminal yang sama persis.
-- =============================================================================
