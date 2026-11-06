<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Administrator Al Ghifari',
                'email' => 'superadmin@alghifari.com',
                'phone' => '081234567890',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Ahmad Fauzi (Admin Operasional)',
                'email' => 'admin@alghifari.com',
                'phone' => '081234567891',
                'role' => 'Admin',
            ],
            [
                'name' => 'Siti Nurhaliza (Staff Jamaah)',
                'email' => 'staff@alghifari.com',
                'phone' => '081234567892',
                'role' => 'Staff',
            ],
            [
                'name' => 'Budi Santoso (Finance & Kasir)',
                'email' => 'finance@alghifari.com',
                'phone' => '081234567893',
                'role' => 'Finance',
            ],
            [
                'name' => 'Haji Muhammad Rizky (Operations Manager)',
                'email' => 'manager@alghifari.com',
                'phone' => '081234567894',
                'role' => 'Manager',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}
