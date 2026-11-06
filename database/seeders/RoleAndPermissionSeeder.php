<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // All Permissions
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Jamaah
            'jamaah.view',
            'jamaah.create',
            'jamaah.edit',
            'jamaah.delete',
            'jamaah.document',
            'jamaah.export',

            // Paket
            'package.view',
            'package.create',
            'package.edit',
            'package.delete',

            // Keberangkatan & Manifest
            'departure.view',
            'departure.create',
            'departure.edit',
            'departure.delete',
            'departure.manifest',

            // Keuangan
            'finance.view',
            'finance.payment',
            'finance.expense',
            'finance.refund',
            'finance.export',
            'finance.cancel',

            // Laporan
            'report.view',
            'report.export',

            // WhatsApp
            'whatsapp.view',
            'whatsapp.send',
            'whatsapp.template',

            // Notifikasi & Audit Log
            'notification.view',
            'audit.view',

            // Pengaturan & User Management
            'setting.view',
            'setting.edit',
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'role.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 1. Super Admin Role
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin Role
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'dashboard.view',
            'jamaah.view', 'jamaah.create', 'jamaah.edit', 'jamaah.document', 'jamaah.export',
            'package.view', 'package.create', 'package.edit',
            'departure.view', 'departure.create', 'departure.edit', 'departure.manifest',
            'finance.view',
            'report.view',
            'whatsapp.view', 'whatsapp.send', 'whatsapp.template',
            'notification.view',
            'user.view',
        ]);

        // 3. Staff Role
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'dashboard.view',
            'jamaah.view', 'jamaah.create', 'jamaah.edit', 'jamaah.document',
            'package.view',
            'departure.view', 'departure.manifest',
            'notification.view',
        ]);

        // 4. Finance Role
        $finance = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'web']);
        $finance->syncPermissions([
            'dashboard.view',
            'jamaah.view',
            'package.view',
            'departure.view',
            'finance.view', 'finance.payment', 'finance.expense', 'finance.refund', 'finance.export',
            'report.view', 'report.export',
            'notification.view',
        ]);

        // 5. Manager Role
        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'dashboard.view',
            'jamaah.view', 'jamaah.export',
            'package.view',
            'departure.view', 'departure.manifest',
            'finance.view', 'finance.export',
            'report.view', 'report.export',
            'audit.view',
            'notification.view',
        ]);
    }
}
