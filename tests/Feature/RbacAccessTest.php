<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    protected User $superAdmin;
    protected User $admin;
    protected User $finance;
    protected User $manager;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::where('email', 'superadmin@alghifari.com')->first();
        $this->admin = User::where('email', 'admin@alghifari.com')->first();
        $this->finance = User::where('email', 'finance@alghifari.com')->first();
        $this->manager = User::where('email', 'manager@alghifari.com')->first();
        $this->staff = User::where('email', 'staff@alghifari.com')->first();
    }

    public function test_all_five_roles_exist_and_can_authenticate(): void
    {
        $this->assertNotNull($this->superAdmin, 'Super Admin user must exist');
        $this->assertNotNull($this->admin, 'Admin user must exist');
        $this->assertNotNull($this->finance, 'Finance user must exist');
        $this->assertNotNull($this->manager, 'Manager user must exist');
        $this->assertNotNull($this->staff, 'Staff user must exist');

        $this->assertTrue($this->superAdmin->hasRole('Super Admin'));
        $this->assertTrue($this->admin->hasRole('Admin'));
        $this->assertTrue($this->finance->hasRole('Finance'));
        $this->assertTrue($this->manager->hasRole('Manager'));
        $this->assertTrue($this->staff->hasRole('Staff'));
    }

    public function test_super_admin_has_unrestricted_access(): void
    {
        $this->actingAs($this->superAdmin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('jamaahs.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('packages.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('departures.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('finance.summary'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('reports.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('whatsapp.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('settings.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('audit-logs.index'))->assertStatus(200);
    }

    public function test_staff_access_restrictions(): void
    {
        // Allowed for staff
        $this->actingAs($this->staff)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('jamaahs.index'))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('jamaahs.create'))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('packages.index'))->assertStatus(200);
        $this->actingAs($this->staff)->get(route('departures.calendar'))->assertStatus(200);

        // Forbidden for staff (HTTP 403)
        $this->actingAs($this->staff)->get(route('finance.summary'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('reports.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('whatsapp.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('settings.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('audit-logs.index'))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('packages.create'))->assertStatus(403);
    }

    public function test_finance_access_restrictions(): void
    {
        // Allowed for finance
        $this->actingAs($this->finance)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->finance)->get(route('finance.summary'))->assertStatus(200);
        $this->actingAs($this->finance)->get(route('finance.invoices.index'))->assertStatus(200);
        $this->actingAs($this->finance)->get(route('finance.payments.index'))->assertStatus(200);
        $this->actingAs($this->finance)->get(route('finance.expenses.index'))->assertStatus(200);
        $this->actingAs($this->finance)->get(route('finance.refunds.index'))->assertStatus(200);
        $this->actingAs($this->finance)->get(route('reports.index'))->assertStatus(200);

        // Forbidden for finance (HTTP 403)
        $this->actingAs($this->finance)->get(route('jamaahs.create'))->assertStatus(403);
        $this->actingAs($this->finance)->get(route('packages.create'))->assertStatus(403);
        $this->actingAs($this->finance)->get(route('departures.create'))->assertStatus(403);
        $this->actingAs($this->finance)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($this->finance)->get(route('settings.index'))->assertStatus(403);
        $this->actingAs($this->finance)->get(route('whatsapp.index'))->assertStatus(403);
    }

    public function test_manager_access_restrictions(): void
    {
        // Allowed for manager
        $this->actingAs($this->manager)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('jamaahs.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('packages.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('departures.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('finance.summary'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('reports.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('audit-logs.index'))->assertStatus(200);

        // Forbidden for manager (HTTP 403)
        $this->actingAs($this->manager)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('settings.index'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('whatsapp.index'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('packages.create'))->assertStatus(403);
    }

    public function test_admin_access_restrictions(): void
    {
        // Allowed for admin
        $this->actingAs($this->admin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('jamaahs.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('packages.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('departures.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('finance.summary'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('reports.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('whatsapp.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('users.index'))->assertStatus(200);

        // Forbidden for admin (HTTP 403)
        $this->actingAs($this->admin)->get(route('settings.index'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('users.roles'))->assertStatus(403);
        $this->actingAs($this->admin)->get(route('audit-logs.index'))->assertStatus(403);
    }
}
