<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Departure;
use App\Models\Expense;
use App\Models\Package;
use App\Models\TransactionCategory;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExpenseEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $unauthorizedUser;
    protected Departure $departure1;
    protected Departure $departure2;
    protected TransactionCategory $category1;
    protected TransactionCategory $category2;
    protected Expense $directExpense;
    protected Expense $generalExpense;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin Finance Test',
            'email'    => 'admin.finance@alghifari.com',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('Super Admin');

        $this->unauthorizedUser = User::create([
            'name'     => 'Staff Tanpa Permission',
            'email'    => 'staff.noperm@alghifari.com',
            'password' => Hash::make('password'),
        ]);

        $package = Package::create([
            'code'          => 'PKG-EXP-' . rand(100, 999),
            'name'          => 'Paket Umrah Expense Test',
            'type'          => 'umrah',
            'price'         => 28000000,
            'quad_price'    => 28000000,
            'duration_days' => 9,
            'status'        => 'aktif',
        ]);

        $this->departure1 = Departure::create([
            'code'           => 'KLT-01-' . rand(100, 999),
            'package_id'     => $package->id,
            'name'           => 'Kloter 01 Test',
            'departure_date' => now()->addDays(10)->format('Y-m-d'),
            'return_date'    => now()->addDays(20)->format('Y-m-d'),
            'quota'          => 45,
            'available_seats'=> 45,
            'status'         => 'persiapan',
        ]);

        $this->departure2 = Departure::create([
            'code'           => 'KLT-02-' . rand(100, 999),
            'package_id'     => $package->id,
            'name'           => 'Kloter 02 Test',
            'departure_date' => now()->addDays(25)->format('Y-m-d'),
            'return_date'    => now()->addDays(35)->format('Y-m-d'),
            'quota'          => 45,
            'available_seats'=> 45,
            'status'         => 'persiapan',
        ]);

        $this->category1 = TransactionCategory::create([
            'name'        => 'Tiket Pesawat Test',
            'type'        => 'pengeluaran',
            'description' => 'Beban tiket pesawat rombongan',
        ]);

        $this->category2 = TransactionCategory::create([
            'name'        => 'Operasional Kantor Test',
            'type'        => 'pengeluaran',
            'description' => 'Beban listrik dan internet kantor',
        ]);

        $this->directExpense = Expense::create([
            'expense_number' => 'EXP-DIR-001',
            'category_id'    => $this->category1->id,
            'departure_id'   => $this->departure1->id,
            'expense_type'   => 'langsung',
            'title'          => 'Uang muka tiket Garuda Kloter 1',
            'amount'         => 15000000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
            'notes'          => 'Catatan pengeluaran tiket',
            'recorded_by'    => $this->admin->id,
            'is_cancelled'   => false,
        ]);

        $this->generalExpense = Expense::create([
            'expense_number' => 'EXP-GEN-001',
            'category_id'    => $this->category2->id,
            'departure_id'   => null,
            'expense_type'   => 'operasional_umum',
            'title'          => 'Langganan internet kantor',
            'amount'         => 3500000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
            'notes'          => 'Catatan beban internet',
            'recorded_by'    => $this->admin->id,
            'is_cancelled'   => false,
        ]);
    }

    public function test_admin_with_finance_expense_permission_can_view_expenses_with_edit_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.expenses'));

        $response->assertStatus(200);
        $response->assertSee('Beban Pengeluaran Operasional');
        $response->assertSee('Langsung');
        $response->assertSee('Operasional Umum');
        $response->assertSee(route('finance.expenses.edit', $this->directExpense->id));
    }

    public function test_admin_can_view_expense_edit_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.expenses.edit', $this->directExpense->id));

        $response->assertStatus(200);
        $response->assertSee('Edit Pengeluaran Operasional');
        $response->assertSee('EXP-DIR-001');
        $response->assertSee('15000000');
        $response->assertSee($this->category1->name);
    }

    public function test_admin_can_update_expense_amount_successfully(): void
    {
        $response = $this->actingAs($this->admin)->put(route('finance.expenses.update', $this->directExpense->id), [
            'title'          => 'Pelunasan tiket Garuda Kloter 1',
            'category_id'    => $this->category1->id,
            'expense_type'   => 'langsung',
            'departure_id'   => $this->departure1->id,
            'amount'         => 18500000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
            'notes'          => 'Catatan diperbarui',
        ]);

        $response->assertRedirect(route('finance.expenses.index'));
        $response->assertSessionHas('toast_success');

        $this->directExpense->refresh();
        $this->assertEquals(18500000, (float) $this->directExpense->amount);
        $this->assertEquals('Pelunasan tiket Garuda Kloter 1', $this->directExpense->title);
    }

    public function test_admin_can_update_expense_category_successfully(): void
    {
        $response = $this->actingAs($this->admin)->put(route('finance.expenses.update', $this->directExpense->id), [
            'title'          => $this->directExpense->title,
            'category_id'    => $this->category2->id,
            'expense_type'   => 'langsung',
            'departure_id'   => $this->departure1->id,
            'amount'         => 15000000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
        ]);

        $response->assertRedirect(route('finance.expenses.index'));
        $this->directExpense->refresh();
        $this->assertEquals($this->category2->id, $this->directExpense->category_id);
    }

    public function test_admin_can_update_expense_departure_for_direct_type(): void
    {
        $response = $this->actingAs($this->admin)->put(route('finance.expenses.update', $this->directExpense->id), [
            'title'          => $this->directExpense->title,
            'category_id'    => $this->category1->id,
            'expense_type'   => 'langsung',
            'departure_id'   => $this->departure2->id,
            'amount'         => 15000000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
        ]);

        $response->assertRedirect(route('finance.expenses.index'));
        $this->directExpense->refresh();
        $this->assertEquals($this->departure2->id, $this->directExpense->departure_id);
    }

    public function test_updating_expense_type_from_direct_to_general_clears_departure_id(): void
    {
        $this->assertNotNull($this->directExpense->departure_id);

        $response = $this->actingAs($this->admin)->put(route('finance.expenses.update', $this->directExpense->id), [
            'title'          => 'Diubah menjadi beban operasional kantor',
            'category_id'    => $this->category2->id,
            'expense_type'   => 'operasional_umum',
            'departure_id'   => $this->departure1->id,
            'amount'         => 15000000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
        ]);

        $response->assertRedirect(route('finance.expenses.index'));
        $this->directExpense->refresh();
        $this->assertEquals('operasional_umum', $this->directExpense->expense_type);
        $this->assertNull($this->directExpense->departure_id);
    }

    public function test_updating_expense_type_from_general_to_direct_requires_valid_departure_id(): void
    {
        $response = $this->actingAs($this->admin)->put(route('finance.expenses.update', $this->generalExpense->id), [
            'title'          => 'Harus gagal karena kloter wajib',
            'category_id'    => $this->category1->id,
            'expense_type'   => 'langsung',
            'departure_id'   => '',
            'amount'         => 3500000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
        ]);

        $response->assertSessionHasErrors(['departure_id']);

        $this->generalExpense->refresh();
        $this->assertEquals('operasional_umum', $this->generalExpense->expense_type);
        $this->assertNull($this->generalExpense->departure_id);
    }

    public function test_user_without_finance_expense_permission_cannot_access_edit_or_update(): void
    {
        $responseEdit = $this->actingAs($this->unauthorizedUser)->get(route('finance.expenses.edit', $this->directExpense->id));
        $responseEdit->assertStatus(403);

        $responseUpdate = $this->actingAs($this->unauthorizedUser)->put(route('finance.expenses.update', $this->directExpense->id), [
            'title'          => 'Percobaan tanpa izin',
            'category_id'    => $this->category1->id,
            'expense_type'   => 'langsung',
            'departure_id'   => $this->departure1->id,
            'amount'         => 99999999,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
        ]);
        $responseUpdate->assertStatus(403);
    }

    public function test_updating_expense_records_audit_log_with_before_and_after_snapshots(): void
    {
        $oldAmount = $this->directExpense->amount;
        $newAmount = 21000000;

        $response = $this->actingAs($this->admin)->put(route('finance.expenses.update', $this->directExpense->id), [
            'title'          => 'Uji pencatatan audit log',
            'category_id'    => $this->category1->id,
            'expense_type'   => 'langsung',
            'departure_id'   => $this->departure1->id,
            'amount'         => $newAmount,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
        ]);

        $response->assertRedirect(route('finance.expenses.index'));

        // Cek log di tabel activity_logs
        $log = ActivityLog::where('action', 'UPDATE_EXPENSE')
            ->where('module', 'Keuangan')
            ->latest('id')
            ->first();

        $this->assertNotNull($log, 'Audit log UPDATE_EXPENSE harus tercatat');
        $this->assertStringContainsString('Memperbarui pengeluaran', $log->description);

        $this->assertEquals((float) $oldAmount, (float) $log->old_data['amount']);
        $this->assertEquals((float) $newAmount, (float) $log->new_data['amount']);
    }

    public function test_cancelled_expense_cannot_be_edited_or_updated(): void
    {
        $this->directExpense->update([
            'is_cancelled'        => true,
            'cancellation_reason' => 'Salah catat',
        ]);

        // Coba buka form edit
        $responseEdit = $this->actingAs($this->admin)->get(route('finance.expenses.edit', $this->directExpense->id));
        $responseEdit->assertRedirect(route('finance.expenses.index'));
        $responseEdit->assertSessionHas('toast_error');

        // Coba kirim update
        $responseUpdate = $this->actingAs($this->admin)->put(route('finance.expenses.update', $this->directExpense->id), [
            'title'          => 'Update gagal',
            'category_id'    => $this->category1->id,
            'expense_type'   => 'langsung',
            'departure_id'   => $this->departure1->id,
            'amount'         => 50000000,
            'expense_date'   => now()->format('Y-m-d'),
            'payment_method' => 'transfer',
        ]);
        $responseUpdate->assertRedirect(route('finance.expenses.index'));
        $responseUpdate->assertSessionHas('toast_error');
    }
}
