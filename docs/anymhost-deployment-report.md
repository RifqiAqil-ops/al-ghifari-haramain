# Laporan Deployment & Audit Keamanan Produksi: Al Ghifari Haramain Management System

Dokumen ini adalah laporan teknis status deployment aplikasi **Al Ghifari Haramain Management System** ke shared hosting **AnymHost** (cPanel) pada domain **`https://alghifariharamain.my.id`**.

---

## 1. Status Komponen Infrastruktur Hosting

| Parameter | Target Spesifikasi | Status Verifikasi | Keterangan |
| :--- | :--- | :---: | :--- |
| **Domain Utama** | `alghifariharamain.my.id` | **PASS** | DNS terhubung ke IP `163.223.227.5` |
| **SSL / HTTPS** | `https://alghifariharamain.my.id` | **PASS** | Port 443 aktif, sertifikat SSL valid |
| **Versi PHP Hosting** | PHP 8.3 | **PASS** | Ekstensi pdo_mysql, mbstring, gd, zip aktif |
| **Home Directory** | `/home/xonkgvsz/` | **PASS** | Terverifikasi |
| **Document Root Publik** | `/home/xonkgvsz/public_html/` | **PASS** | Folder publik web terisolasi |
| **Core Directory** | `/home/xonkgvsz/al-ghifari-core/` | **PASS** | Di luar `public_html` (tidak dapat diakses web) |
| **Database Server** | MySQL / MariaDB (Localhost via UNIX Socket)| **PASS** | Database: `xonkgvsz_alghifari` |
| **Database User** | `xonkgvsz_aghuser` | **PASS** | ALL PRIVILEGES aktif, SQL lokal terimpor |

---

## 2. Audit Keamanan & Proteksi Dokumen Jamaah

| Aspek Keamanan | Mekanisme Proteksi | Status |
| :--- | :--- | :---: |
| **Isolasi Source Code** | Seluruh kode sumber, vendor, dan file `.env` diletakkan di `/home/xonkgvsz/al-ghifari-core/`. Web server hanya mengekspos `public_html/`. | **PASS** |
| **Proteksi Dokumen Jamaah (KTP, KK, Paspor)** | 1. URL direktori `storage/jamaah_documents/` diblokir permanen via `.htaccess` (`RewriteRule ^storage/jamaah_documents/ - [F,L]`). Percobaan akses langsung melalui URL akan ditolak HTTP 403 Forbidden.<br>2. Pengunduhan dokumen diatur melalui controller resmi (`JamaahController@downloadDocument`) yang dilindungi otentikasi session dan permission `jamaah.document`. | **PASS** |
| **Proteksi File Sensitif** | Rule `.htaccess` otomatis memblokir akses ke dotfiles (`.env`, `.git`), file `.sql`, dan direktori tersembunyi. | **PASS** |
| **Keamanan Session & Cookie** | `SESSION_DRIVER=database` dan `SESSION_SECURE_COOKIE=true` memastikan cookie autentikasi hanya dikirim lewat koneksi HTTPS. | **PASS** |
| **Kunci Enkripsi (`APP_KEY`)** | Konsisten terkonfigurasi di server hosting (`.env`). | **PASS** |
| **Kredensial Rahasia** | Tidak ada password atau token yang dicatat dalam log, skrip publik, atau laporan. | **PASS** |

---

## 3. Status Paket Deployment

| File Paket | Ukuran | Lokasi Lokal | Status |
| :--- | :---: | :--- | :---: |
| **`al-ghifari-core.zip`** | 40.95 MB | `deployment\packages\al-ghifari-core.zip` | **TERPASANG DI HOSTING** (`/home/xonkgvsz/al-ghifari-core/`) |
| **`al_ghifari_database.sql`** | 160 KB | `deployment\packages\al_ghifari_database.sql` | **BERHASIL DIIMPOR KE PHPMYADMIN** |
| **`public_html.zip`** | 607 KB | `deployment\packages\public_html.zip` | **SIAP DIEKSTRAK KE `public_html/`** |

---

## 4. Rencana Eksekusi Akhir di cPanel

1. **Pemasangan `public_html.zip`:** Ekstrak file publik ke `/home/xonkgvsz/public_html/`.
2. **Penyambungan Storage Symlink:** Jalankan `php artisan storage:link` dari Terminal cPanel.
3. **Optimasi Cache Produksi:** Jalankan `php artisan optimize` dari Terminal cPanel.
4. **Pendaftaran Akun Owner:** Jalankan `php artisan owner:setup` dari Terminal cPanel (input nama, email, dan password tersembunyi).
5. **Verifikasi Web Online:** Pengujian langsung via browser ke `https://alghifariharamain.my.id`.
