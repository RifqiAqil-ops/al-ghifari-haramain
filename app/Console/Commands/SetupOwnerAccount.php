<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SetupOwnerAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'owner:setup 
                            {--name= : Nama lengkap pemilik perusahaan}
                            {--email= : Alamat email pemilik}
                            {--password= : Password (opsional, jika kosong akan diminta secara aman/hidden)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyiapkan role Owner dan membuat/memperbarui akun Pemilik Perusahaan dengan aman';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Setup Akun & Role Pemilik Perusahaan (Al Ghifari Haramain) ===');

        // 1. Reset Spatie Permission Cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat atau dapatkan Role Owner
        $ownerRole = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);

        // 3. Sinkronisasi permissions untuk Owner (Akses eksekutif penuh terhadap seluruh sistem)
        $allPermissions = Permission::all();
        $ownerRole->syncPermissions($allPermissions);
        $this->info("✓ Role 'Owner' berhasil disiapkan dengan {$allPermissions->count()} hak akses (full executive access).");

        // 4. Input Nama
        $name = $this->option('name');
        if (empty($name)) {
            $name = $this->ask('Masukkan Nama Lengkap Pemilik', 'Owner Al Ghifari Haramain');
        }

        // 5. Input Email
        $email = $this->option('email');
        while (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $this->ask('Masukkan Alamat Email Pemilik');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Format email tidak valid. Silakan coba lagi.');
            }
        }

        // 6. Input Password secara aman (hidden input)
        $password = $this->option('password');
        if (empty($password)) {
            $password = $this->secret('Masukkan Password Baru untuk Akun Pemilik (input disembunyikan)');
            $confirmPassword = $this->secret('Konfirmasi Password');

            if ($password !== $confirmPassword) {
                $this->error('Konfirmasi password tidak cocok! Pembatalan operasi.');
                return 1;
            }
        }

        if (strlen($password) < 8) {
            $this->error('Password minimal harus memiliki panjang 8 karakter demi keamanan!');
            return 1;
        }

        // 7. Buat atau perbarui akun user
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->is_active = true;
        $user->save();

        // 8. Assign Role Owner
        $user->syncRoles([$ownerRole]);

        $this->newLine();
        $this->info("=================================================");
        $this->info("✓ SUKSES: Akun Pemilik Perusahaan berhasil dibuat!");
        $this->info("  Nama  : {$user->name}");
        $this->info("  Email : {$user->email}");
        $this->info("  Role  : Owner (Akses Seluruh Modul Aktif)");
        $this->info("  Status: Aktif");
        $this->info("=================================================");
        $this->comment("Catatan: Password telah di-hash secara aman menggunakan Bcrypt dan tidak disimpan dalam teks biasa.");

        return 0;
    }
}

