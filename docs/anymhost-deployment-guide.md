# Panduan Lengkap Deployment Al Ghifari Haramain ke cPanel AnymHost

Dokumen ini disusun khusus sebagai panduan langkah demi langkah proses deployment aplikasi **Al Ghifari Haramain Management System** (Laravel 12 + PHP 8.3) ke shared hosting **AnymHost** dengan control panel **cPanel**.

---

## 1. Ringkasan Informasi Hosting & Target Deployment

| Parameter | Konfigurasi Target |
| :--- | :--- |
| **Domain** | `https://alghifariharamain.my.id` |
| **Hosting Control Panel** | cPanel (AnymHost) |
| **Versi PHP Target** | **PHP 8.3** |
| **cPanel Username** | `xonkgvsz` |
| **cPanel Home Path** | `/home/xonkgvsz/` |
| **Web Root (Public)** | `/home/xonkgvsz/public_html/` |
| **Core App Root (Private)**| `/home/xonkgvsz/al-ghifari-core/` |
| **Database Name** | `xonkgvsz_alghifari` |
| **Database Username** | `xonkgvsz_aghuser` |

---

## 2. Struktur Arsitektur Keamanan (Security Hardened)

Pada shared hosting standar, meletakkan seluruh file project Laravel di dalam folder `public_html` sangat berisiko karena file sensitif seperti `.env`, logs, dan source code PHP berpotensi diakses secara langsung lewat browser jika terjadi kesalahan konfigurasi server.

Oleh karena itu, arsitektur deployment ini memisahkan file sistem menjadi **dua zona terisolasi**:

```
/home/xonkgvsz/
├── al-ghifari-core/             <-- ZONA PRIVATE (Di luar public_html, TIDAK BISA diakses publik)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/                  <-- Dependensi Composer lengkap
│   ├── .env                     <-- File credential rahasia & koneksi DB
│   └── artisan
│
└── public_html/                 <-- ZONA PUBLIK (Hanya mengekspos aset web)
    ├── assets/                  <-- File gambar statis & logo
    ├── build/                   <-- Aset CSS/JS hasil compile Vite
    ├── .htaccess                <-- Proteksi keamanan, Rewrite Laravel, Force HTTPS
    ├── index.php                <-- Jembatan bootstrap ke ../al-ghifari-core/
    ├── robots.txt
    ├── favicon.ico
    └── symlink_storage.php      <-- Helper aman sekali pakai untuk membuat storage symlink
```

---

## 3. Paket Deployment yang Sudah Disiapkan

Semua paket deployment yang telah diaudit, dibersihkan dari kredensial lokal, dan siap diunggah tersimpan di folder lokal:
`d:\algifari m\al-ghifari-haramain\deployment\packages\`

1. **`al_ghifari_database.sql`** (±160 KB):
   Dump database lokal yang bersih dari instruksi `CREATE DATABASE` atau `USE` sehingga 100% kompatibel saat diimpor ke database cPanel ber-prefix (`xonkgvsz_alghifari`).
2. **`al-ghifari-core.zip`** (±37 MB):
   Berisi seluruh source code Laravel, dependensi `vendor/` PHP 8.3/8.2, template `.env.production.example`, dan artisan. (Bebas dari `.git`, `node_modules`, `tests`, dan `.env` lokal).
3. **`public_html.zip`** (±600 KB):
   Berisi hasil build produksi Vite (`manifest.json`, CSS, JS), logo aset, `index.php` yang sudah diarahkan ke core, `.htaccess` security-hardened, dan skrip helper symlink.

---

## 4. Langkah demi Langkah Pelaksanaan Deployment

### Langkah 1: Verifikasi Versi PHP & Ekstensi di cPanel
1. Login ke cPanel AnymHost Anda.
2. Cari menu **Select PHP Version** atau **MultiPHP Manager**.
3. Pastikan domain `alghifariharamain.my.id` menggunakan **PHP 8.3**.
4. Buka tab **Extensions** / **PHP Extensions** dan pastikan ekstensi berikut berstatus centang (aktif):
   - `bcmath`
   - `ctype`
   - `curl`
   - `fileinfo`
   - `gd`
   - `json`
   - `mbstring`
   - `openssl`
   - `pdo_mysql`
   - `tokenizer`
   - `xml`
   - `zip`
5. *(Rekomendasi)* Buka tab **Options** dan pastikan:
   - `memory_limit` minimal `256M` (disarankan `512M`).
   - `upload_max_filesize` minimal `20M` (untuk upload dokumen jamaah).
   - `post_max_size` minimal `25M`.

---

### Langkah 2: Pembuatan Database & User di cPanel
1. Di cPanel, buka menu **MySQL Database Wizard** (atau **MySQL® Databases**).
2. **Step 1 - Create A Database**:
   - Masukkan nama: `alghifari`
   - cPanel akan otomatis menambahkan prefix sehingga nama database menjadi: `xonkgvsz_alghifari`.
   - Klik **Next Step**.
3. **Step 2 - Create Database Users**:
   - Username: `aghuser` (nama lengkap: `xonkgvsz_aghuser`).
   - Password: Buat password yang kuat melalui tombol *Password Generator* (simpan password ini di catatan aman sementara).
   - Klik **Create User**.
4. **Step 3 - Add User to Database**:
   - Centang opsi **ALL PRIVILEGES**.
   - Klik **Make Changes**.

---

### Langkah 3: Import Database Lokal ke cPanel phpMyAdmin
1. Kembali ke beranda cPanel, buka **phpMyAdmin**.
2. Di panel sebelah kiri, klik database `xonkgvsz_alghifari`.
3. Klik tab **Import** pada menu navigasi atas.
4. Klik tombol **Choose File** (Pilih File) dan pilih file:
   `deployment\packages\al_ghifari_database.sql`
5. Pastikan format terpilih adalah **SQL**.
6. Gulir ke bawah dan klik tombol **Import** (atau **Go**).
7. Tunggu hingga muncul pesan sukses berwarna hijau: *"Import has been successfully finished..."*.
8. Verifikasi bahwa tabel-tabel seperti `users`, `jamaahs`, `packages`, `payments`, `documents`, dll. telah muncul beserta data awalnya.

---

### Langkah 4: Unggah & Ekstrak Paket Core (`al-ghifari-core.zip`)
1. Di cPanel, buka menu **File Manager**.
2. Pastikan Anda berada di direktori root `/home/xonkgvsz/` (satu level di atas `public_html`).
3. Buat folder baru bernama `al-ghifari-core` (klik tombol **+ Folder** di pojok kiri atas).
4. Masuk ke dalam folder `al-ghifari-core`.
5. Klik tombol **Upload** di bilah atas.
6. Unggah file `deployment\packages\al-ghifari-core.zip`.
7. Setelah upload 100% selesai (bar berwarna hijau), kembali ke File Manager dan klik **Reload**.
8. Klik kanan pada file `al-ghifari-core.zip`, lalu pilih **Extract** (ekstrak ke `/home/xonkgvsz/al-ghifari-core`).
9. Hapus file `al-ghifari-core.zip` setelah diekstrak untuk menghemat kuota hosting.

---

### Langkah 5: Konfigurasi File Lingkungan Produksi (`.env`)
1. Di dalam folder `/home/xonkgvsz/al-ghifari-core/`, cari file `.env.production.example`.
2. Klik kanan file `.env.production.example` lalu pilih **Rename**, ubah namanya menjadi **`.env`**.
   *(Catatan: Jika file yang berawalan titik tidak muncul, klik tombol **Settings** di pojok kanan atas File Manager dan centang "Show Hidden Files (dotfiles)")*.
3. Klik kanan file `.env` yang baru di-rename, lalu pilih **Edit**.
4. Sesuaikan parameter berikut:
   ```dotenv
   APP_NAME="Al Ghifari Haramain"
   APP_ENV=production
   APP_KEY=base64:7B5/k/X2m8t9yU9q0W1e2r3t4y5u6i7o8p9a0s1d2f3=  <-- Gunakan APP_KEY yang sudah ada atau generate baru
   APP_DEBUG=false
   APP_URL=https://alghifariharamain.my.id

   LOG_CHANNEL=daily
   LOG_LEVEL=error

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=xonkgvsz_alghifari
   DB_USERNAME=xonkgvsz_aghuser
   DB_PASSWORD=MASUKKAN_PASSWORD_USER_DATABASE_ANDA_DISINI

   SESSION_DRIVER=database
   SESSION_LIFETIME=120
   SESSION_ENCRYPT=false
   SESSION_PATH=/
   SESSION_DOMAIN=null
   SESSION_SECURE_COOKIE=true

   FILESYSTEM_DISK=public
   ```
5. Klik **Save Changes**.

---

### Langkah 6: Unggah & Ekstrak Paket Publik (`public_html.zip`)
1. Di cPanel File Manager, navigasi ke dalam folder **`public_html/`**.
2. Jika ada file default bawaan hosting seperti `default.html` atau `index.html` kosong, Anda dapat menghapusnya atau memindahkannya ke folder backup.
3. Klik tombol **Upload** di bilah atas.
4. Unggah file `deployment\packages\public_html.zip`.
5. Setelah selesai 100%, kembali ke File Manager dan klik **Reload**.
6. Klik kanan pada `public_html.zip`, lalu pilih **Extract** langsung ke `/home/xonkgvsz/public_html`.
7. Pastikan struktur isi `public_html/` adalah sebagai berikut:
   - `assets/` (folder)
   - `build/` (folder)
   - `.htaccess`
   - `favicon.ico`
   - `index.php`
   - `robots.txt`
   - `symlink_storage.php`
8. Hapus file `public_html.zip` setelah diekstrak.

---

### Langkah 7: Pembuatan Symlink Folder Storage (Dokumen & Bukti Pembayaran)
Agar dokumen jamaah dan bukti pembayaran dapat diakses melalui web secara aman, folder `storage/app/public` harus terhubung ke `public_html/storage`.

Ada dua opsi untuk melakukannya:

#### Opsi A: Menggunakan Helper Web Aman (Paling Mudah, Tanpa SSH)
1. Buka browser Anda dan akses URL berikut:
   ```
   https://alghifariharamain.my.id/symlink_storage.php?token=alghifari_setup_2026
   ```
2. Anda akan melihat pesan konfirmasi:
   > **SUCCESS:** Storage symlink successfully created: `/home/xonkgvsz/public_html/storage` -> `/home/xonkgvsz/al-ghifari-core/storage/app/public`.
3. **PENTING:** Demi keamanan, segera hapus file `symlink_storage.php` di dalam `public_html/` melalui File Manager setelah symlink berhasil dibuat!

#### Opsi B: Menggunakan Fitur Terminal di cPanel (Jika Tersedia)
1. Buka menu **Terminal** di cPanel.
2. Jalankan perintah:
   ```bash
   cd /home/xonkgvsz/al-ghifari-core
   php artisan storage:link
   php artisan optimize
   ```

---

### Langkah 8: Pengujian Akhir & Verifikasi Fungsionalitas
Lakukan pengujian berikut di browser Anda:
1. Buka `https://alghifariharamain.my.id` di browser. Pastikan halaman landing/login muncul dengan tampilan rapi, SSL/HTTPS aktif (ikon gembok hijau/aman), dan tidak ada error CSS/JS di console pengembang (F12).
2. Lakukan login menggunakan akun kredensial yang ada di database lokal:
   - Pastikan dashboard terbuka dengan lancar.
   - Pastikan data jamaah, paket, manifest, dan pembayaran tampil lengkap sesuai data lokal.
3. Uji coba fitur-fitur kritis:
   - Tambah paket atau edit data jamaah.
   - Export laporan Excel dan PDF (verifikasi hasil unduhan rapi).
   - Upload dokumen pendukung pada data jamaah.

---

## 5. Troubleshooting & Solusi Kendala cPanel

| Gejala Kendala | Kemungkinan Penyebab | Solusi Praktis |
| :--- | :--- | :--- |
| **Error 500 (Internal Server Error)** | Izin folder (permissions) atau file `.env` salah konfigurasi. | Pastikan folder `al-ghifari-core/storage` dan `al-ghifari-core/bootstrap/cache` memiliki permission `755` atau `775`. Periksa error log di `al-ghifari-core/storage/logs/laravel.log`. |
| **Halaman Blank Putih** | Versi PHP belum diubah ke 8.3 atau ekstensi PDO MySQL belum aktif. | Masuk ke cPanel > Select PHP Version > Pilih PHP 8.3 dan aktifkan ekstensi `pdo_mysql`, `mbstring`, `fileinfo`. |
| **Aset Tampilan Rusak / CSS Tidak Terbaca** | Symlink build atau URL belum HTTPS. | Pastikan `APP_URL` di `.env` bernilai `https://alghifariharamain.my.id`. Pastikan folder `build/` ada di dalam `public_html`. |
| **Gambar Dokumen Jamaah 404** | Storage symlink belum dibuat. | Jalankan langkah 7 (akses `symlink_storage.php` dengan token atau jalankan `php artisan storage:link`). |
| **Database Connection Refused / Access Denied** | Password user DB di `.env` salah, atau user belum dihubungkan ke database. | Buka cPanel > MySQL Databases > Pastikan user `xonkgvsz_aghuser` terdaftar di bawah `xonkgvsz_alghifari` dengan privilege `ALL PRIVILEGES`. |

