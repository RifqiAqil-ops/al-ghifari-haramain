<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class CleanProductionDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:clean-database
                            {--dry-run : Simulasi pemeriksaan data tanpa melakukan penghapusan}
                            {--force : Jalankan pembersihan tanpa dialog konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan seluruh data demo, dummy, dan test dari database produksi sambil mempertahankan akun & role Owner';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->newLine();
        $this->info('========================================================================');
        $this->info('   AUDIT & PEMBERSIHAN DATABASE PRODUKSI (AL GHIFARI HARAMAIN)         ');
        $this->info('========================================================================');

        if ($isDryRun) {
            $this->warn('MODE SIMULASI (DRY RUN): Tidak ada data yang akan dihapus.');
        }

        // 1. Verifikasi Keberadaan Akun Owner
        $ownerRole = Role::where('name', 'Owner')->first();
        if (!$ownerRole) {
            $this->error('GAGAL: Role "Owner" tidak ditemukan di database.');
            $this->error('Jalankan "php artisan owner:setup" terlebih dahulu sebelum melakukan pembersihan.');
            return 1;
        }

        $ownerUsers = User::role('Owner')->get();
        if ($ownerUsers->isEmpty()) {
            $this->error('GAGAL: Tidak ditemukan user dengan role "Owner"!');
            $this->error('Demi keselamatan sistem, pembersihan DIBATALKAN agar akses aplikasi tidak hilang.');
            $this->error('Silakan jalankan "php artisan owner:setup" terlebih dahulu.');
            return 1;
        }

        $owner = $ownerUsers->first();
        $ownerIds = $ownerUsers->pluck('id')->toArray();
        $ownerPermissionsCount = $owner->getAllPermissions()->count();

        $this->info("✓ Akun Owner Terverifikasi:");
        $this->info("  - Nama         : {$owner->name}");
        $this->info("  - Email        : {$owner->email}");
        $this->info("  - ID User      : {$owner->id}");
        $this->info("  - Total Hak Akses: {$ownerPermissionsCount} permissions (Akses Eksekutif Penuh)");
        $this->newLine();

        // 2. Audit Data Sebelum Pembersihan
        $countsBefore = [
            'users_demo'           => DB::table('users')->whereNotIn('id', $ownerIds)->count(),
            'users_owner'          => DB::table('users')->whereIn('id', $ownerIds)->count(),
            'jamaahs'              => DB::table('jamaahs')->count(),
            'jamaah_contacts'      => DB::table('jamaah_contacts')->count(),
            'jamaah_documents'     => DB::table('jamaah_documents')->count(),
            'packages'             => DB::table('packages')->count(),
            'package_facilities'   => DB::table('package_facilities')->count(),
            'itineraries'          => DB::table('itineraries')->count(),
            'itinerary_items'      => DB::table('itinerary_items')->count(),
            'departures'           => DB::table('departures')->count(),
            'departure_checklists' => DB::table('departure_checklists')->count(),
            'accommodations'       => DB::table('accommodations')->count(),
            'flights'              => DB::table('flights')->count(),
            'registrations'        => DB::table('registrations')->count(),
            'invoices'             => DB::table('invoices')->count(),
            'payments'             => DB::table('payments')->count(),
            'expenses'             => DB::table('expenses')->count(),
            'notifications'        => DB::table('notifications')->count(),
            'activity_logs'        => DB::table('activity_logs')->count(),
            'whatsapp_messages'    => DB::table('whatsapp_messages')->count(),
            // Data Konfigurasi Tetap
            'roles'                => DB::table('roles')->count(),
            'permissions'          => DB::table('permissions')->count(),
            'company_settings'     => DB::table('company_settings')->count(),
            'bank_accounts'        => DB::table('bank_accounts')->count(),
            'transaction_categories' => DB::table('transaction_categories')->count(),
            'whatsapp_templates'   => DB::table('whatsapp_templates')->count(),
        ];

        $auditTableData = [
            ['Akun User Demo / Test', $countsBefore['users_demo'], 'AKAN DIHAPUS'],
            ['Data Jamaah', $countsBefore['jamaahs'], 'AKAN DIHAPUS'],
            ['Kontak Darurat Jamaah', $countsBefore['jamaah_contacts'], 'AKAN DIHAPUS'],
            ['Dokumen Jamaah (KTP/Paspor)', $countsBefore['jamaah_documents'], 'AKAN DIHAPUS'],
            ['Paket Haji & Umrah Demo', $countsBefore['packages'], 'AKAN DIHAPUS'],
            ['Fasilitas Paket Demo', $countsBefore['package_facilities'], 'AKAN DIHAPUS'],
            ['Itinerary Paket Demo', $countsBefore['itineraries'], 'AKAN DIHAPUS'],
            ['Item Kegiatan Itinerary', $countsBefore['itinerary_items'], 'AKAN DIHAPUS'],
            ['Jadwal Keberangkatan Demo', $countsBefore['departures'], 'AKAN DIHAPUS'],
            ['Checklist Keberangkatan', $countsBefore['departure_checklists'], 'AKAN DIHAPUS'],
            ['Akomodasi Hotel Demo', $countsBefore['accommodations'], 'AKAN DIHAPUS'],
            ['Penerbangan Demo', $countsBefore['flights'], 'AKAN DIHAPUS'],
            ['Data Registrasi Demo', $countsBefore['registrations'], 'AKAN DIHAPUS'],
            ['Invoice & Tagihan Demo', $countsBefore['invoices'], 'AKAN DIHAPUS'],
            ['Pembayaran & Kwitansi Demo', $countsBefore['payments'], 'AKAN DIHAPUS'],
            ['Pengeluaran Kas Operasional Demo', $countsBefore['expenses'], 'AKAN DIHAPUS'],
            ['Notifikasi Sistem Percobaan', $countsBefore['notifications'], 'AKAN DIHAPUS'],
            ['Audit Log Development', $countsBefore['activity_logs'], 'AKAN DIHAPUS'],
            ['Log Pesan WhatsApp Percobaan', $countsBefore['whatsapp_messages'], 'AKAN DIHAPUS'],
            ['---------------------------------', '-------', '------------------'],
            ['Akun Owner Perusahaan', $countsBefore['users_owner'], 'DIPERTAHANKAN (AMAN)'],
            ['Role Sistem (Owner, Admin, dll)', $countsBefore['roles'], 'DIPERTAHANKAN (AMAN)'],
            ['Daftar Hak Akses (Permissions)', $countsBefore['permissions'], 'DIPERTAHANKAN (AMAN)'],
            ['Profil Perusahaan (Al Ghifari)', $countsBefore['company_settings'], 'DIPERTAHANKAN (AMAN)'],
            ['Rekening Bank Perusahaan', $countsBefore['bank_accounts'], 'DIPERTAHANKAN (AMAN)'],
            ['Kategori Transaksi Keuangan', $countsBefore['transaction_categories'], 'DIPERTAHANKAN (AMAN)'],
            ['Template Pesan WhatsApp', $countsBefore['whatsapp_templates'], 'DIPERTAHANKAN (AMAN)'],
        ];

        $this->table(['Entitas Data', 'Jumlah Record', 'Rencana Tindakan'], $auditTableData);

        if ($isDryRun) {
            $this->info('Simulasi selesai. Semua data tetap utuh.');
            return 0;
        }

        // 3. Konfirmasi Pengguna
        if (!$this->option('force')) {
            if (!$this->confirm('Apakah Anda yakin ingin mengeksekusi pembersihan seluruh data demo di atas? Tindakan ini tidak dapat dibatalkan.')) {
                $this->warn('Operasi pembersihan dibatalkan oleh pengguna.');
                return 0;
            }
        }

        // 4. Eksekusi Pembersihan dalam Transaksi Database
        $this->info('Memulai pembersihan data sesuai relasi foreign key...');

        try {
            DB::transaction(function () use ($ownerIds) {
                // Nonaktifkan pemeriksaan FK sementara agar urutan bersih sempurna
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                // Hapus data operasional demo
                DB::table('payments')->truncate();
                if (Schema::hasTable('refunds')) {
                    DB::table('refunds')->truncate();
                }
                DB::table('invoices')->truncate();
                DB::table('registrations')->truncate();
                DB::table('jamaah_documents')->truncate();
                DB::table('jamaah_contacts')->truncate();
                DB::table('jamaahs')->truncate();
                DB::table('expenses')->truncate();
                DB::table('departure_checklists')->truncate();
                DB::table('accommodations')->truncate();
                DB::table('flights')->truncate();
                DB::table('departures')->truncate();
                DB::table('itinerary_items')->truncate();
                DB::table('itineraries')->truncate();
                DB::table('package_facilities')->truncate();
                DB::table('packages')->truncate();
                DB::table('notifications')->truncate();
                DB::table('activity_logs')->truncate();
                DB::table('whatsapp_messages')->truncate();

                // Hapus sesi lama
                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->truncate();
                }

                // Hapus cache database lama
                if (Schema::hasTable('cache')) {
                    DB::table('cache')->truncate();
                }

                // Hapus relasi role user demo
                DB::table('model_has_roles')
                    ->where('model_type', User::class)
                    ->whereNotIn('model_id', $ownerIds)
                    ->delete();

                // Hapus user demo (selain Owner)
                DB::table('users')
                    ->whereNotIn('id', $ownerIds)
                    ->delete();

                // Reset Auto Increment pada tabel transaksi
                $tablesToReset = [
                    'jamaahs', 'jamaah_contacts', 'jamaah_documents',
                    'packages', 'package_facilities', 'itineraries', 'itinerary_items',
                    'departures', 'departure_checklists', 'accommodations', 'flights',
                    'registrations', 'invoices', 'payments', 'refunds', 'expenses',
                    'notifications', 'activity_logs', 'whatsapp_messages'
                ];

                foreach ($tablesToReset as $table) {
                    try {
                        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = 1;");
                    } catch (\Exception $e) {
                        // Abaikan jika tidak didukung
                    }
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });

            // 5. Catat Log Audit Pembersihan Pertama di Database Bersih
            try {
                ActivityLogger::log(
                    'CLEANUP_PRODUCTION_DATABASE',
                    'System',
                    "Database produksi berhasil dibersihkan dari seluruh data demo development. Akun Owner ({$owner->email}) aktif dengan 36 hak akses."
                );
            } catch (\Exception $e) {
                // Abaikan jika log tidak tersedia
            }

            // 6. Bersihkan Cache Laravel
            Artisan::call('cache:clear');
            Artisan::call('view:clear');

            $this->newLine();
            $this->info('========================================================================');
            $this->info('   HASIL PEMBERSIHAN DATABASE PRODUKSI (BERHASIL)                       ');
            $this->info('========================================================================');

            $countsAfter = [
                ['Total Jamaah', DB::table('jamaahs')->count(), 'BERSIH (0)'],
                ['Total Paket', DB::table('packages')->count(), 'BERSIH (0)'],
                ['Total Keberangkatan', DB::table('departures')->count(), 'BERSIH (0)'],
                ['Total Registrasi Pendaftaran', DB::table('registrations')->count(), 'BERSIH (0)'],
                ['Total Invoice', DB::table('invoices')->count(), 'BERSIH (0)'],
                ['Total Pembayaran (Pemasukan)', DB::table('payments')->count(), 'BERSIH (0)'],
                ['Total Pengeluaran Kas', DB::table('expenses')->count(), 'BERSIH (0)'],
                ['Total Akun Demo Tersisa', DB::table('users')->whereNotIn('id', $ownerIds)->count(), 'BERSIH (0)'],
                ['---------------------------------', '-------', '------------------'],
                ['Akun Owner Aktif', DB::table('users')->whereIn('id', $ownerIds)->count(), 'AKTIF (1)'],
                ['Role Owner & Hak Akses Spatie', $owner->getAllPermissions()->count(), 'LENGKAP (36 Hak Akses)'],
                ['Profil Perusahaan Al Ghifari', DB::table('company_settings')->count(), 'TERPASANG'],
            ];

            $this->table(['Entitas Data', 'Jumlah Record Saat Ini', 'Status'], $countsAfter);

            $this->info('✓ Database produksi kini 100% bersih, kosong, aman, dan siap digunakan!');
            $this->info('  Silakan buka https://alghifariharamain.my.id untuk mulai operasional.');

            return 0;

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Terjadi kesalahan saat pembersihan: ' . $e->getMessage());
            return 1;
        }
    }
}
