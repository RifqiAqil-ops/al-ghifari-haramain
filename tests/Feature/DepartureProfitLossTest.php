<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Services\ProfitLossService;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DepartureProfitLossTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $unauthorizedUser;
    protected Package $packageA;
    protected Package $packageB;
    protected Departure $kloter1;
    protected Departure $kloter2;
    protected Departure $kloter3;
    protected TransactionCategory $catDirect;
    protected TransactionCategory $catGeneral;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::create([
            'name'     => 'Admin Report Test',
            'email'    => 'admin.report@alghifari.com',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('Super Admin');

        $this->unauthorizedUser = User::create([
            'name'     => 'Staff Tanpa Permission Report',
            'email'    => 'staff.noreport@alghifari.com',
            'password' => Hash::make('password'),
        ]);

        $this->packageA = Package::create([
            'code'          => 'PKG-A-' . rand(100, 999),
            'name'          => 'Paket Reguler 9 Hari',
            'type'          => 'umrah',
            'price'         => 30000000,
            'quad_price'    => 30000000,
            'duration_days' => 9,
            'status'        => 'aktif',
        ]);

        $this->packageB = Package::create([
            'code'          => 'PKG-B-' . rand(100, 999),
            'name'          => 'Paket VIP 12 Hari',
            'type'          => 'umrah',
            'price'         => 45000000,
            'quad_price'    => 45000000,
            'duration_days' => 12,
            'status'        => 'aktif',
        ]);

        $this->kloter1 = Departure::create([
            'code'            => 'KLT-A1-' . rand(100, 999),
            'package_id'      => $this->packageA->id,
            'name'            => 'Rombongan Jakarta A1',
            'departure_date'  => '2026-03-01',
            'return_date'     => '2026-03-10',
            'quota'           => 50,
            'available_seats' => 50,
            'status'          => 'persiapan',
        ]);

        $this->kloter2 = Departure::create([
            'code'            => 'KLT-A2-' . rand(100, 999),
            'package_id'      => $this->packageA->id,
            'name'            => 'Rombongan Surabaya A2',
            'departure_date'  => '2026-03-15',
            'return_date'     => '2026-03-24',
            'quota'           => 50,
            'available_seats' => 50,
            'status'          => 'persiapan',
        ]);

        $this->kloter3 = Departure::create([
            'code'            => 'KLT-B1-' . rand(100, 999),
            'package_id'      => $this->packageB->id,
            'name'            => 'Rombongan VIP B1',
            'departure_date'  => '2026-04-01',
            'return_date'     => '2026-04-12',
            'quota'           => 40,
            'available_seats' => 40,
            'status'          => 'persiapan',
        ]);

        $this->catDirect = TransactionCategory::create([
            'name' => 'Tiket Pesawat & Visa',
            'type' => 'pengeluaran',
        ]);

        $this->catGeneral = TransactionCategory::create([
            'name' => 'Sewa Kantor & Gaji',
            'type' => 'pengeluaran',
        ]);
    }

    private function createJamaahWithRegistration(Departure $departure, float $finalPrice = 30000000, string $status = 'terdaftar'): Registration
    {
        $jamaah = Jamaah::create([
            'registration_number' => 'JMH-' . uniqid(),
            'name'                => 'Jamaah Test ' . uniqid(),
            'nik'                 => '3201' . rand(100000000000, 999999999999),
            'gender'              => 'L',
            'birth_place'         => 'Jakarta',
            'birth_date'          => '1990-01-01',
            'phone'               => '0812' . rand(10000000, 99999999),
            'address'             => 'Alamat Test',
            'status'              => 'aktif',
        ]);

        $reg = Registration::create([
            'registration_number' => 'REG-' . uniqid(),
            'jamaah_id'           => $jamaah->id,
            'package_id'          => $departure->package_id,
            'departure_id'        => $departure->id,
            'registration_date'   => now()->format('Y-m-d'),
            'room_type'           => 'quad',
            'agreed_price'        => $finalPrice,
            'discount'            => 0,
            'final_price'         => $finalPrice,
            'status'              => $status,
            'payment_status'      => 'belum_bayar',
            'created_by'          => $this->admin->id,
        ]);

        Invoice::create([
            'invoice_number'  => 'INV-' . uniqid(),
            'registration_id' => $reg->id,
            'total_amount'    => $finalPrice,
            'paid_amount'     => 0,
            'remaining_amount'=> $finalPrice,
            'status'          => 'belum_bayar',
            'issue_date'      => now()->format('Y-m-d'),
            'due_date'        => now()->addDays(30)->format('Y-m-d'),
        ]);

        return $reg;
    }

    private function createExpense(array $attributes): Expense
    {
        return Expense::create(array_merge([
            'expense_number' => 'EXP-' . uniqid(),
            'title'          => 'Beban Operasional Test',
            'payment_method' => 'transfer',
            'is_cancelled'   => false,
            'recorded_by'    => $this->admin->id,
        ], $attributes));
    }

    public function test_kloter_revenue_calculated_from_active_registrations_contract_values(): void
    {
        // Kloter 1: 3 jamaah aktif @ 30.000.000 = 90.000.000
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        // Kloter 1: 1 jamaah batal @ 30.000.000 (tidak boleh dihitung)
        $this->createJamaahWithRegistration($this->kloter1, 30000000, 'batal');

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'departure_id' => $this->kloter1->id,
        ]);

        $kloterData = $report['kloters']->get($this->kloter1->id);
        $this->assertEquals(3, $kloterData['active_jamaah_count']);
        $this->assertEquals(90000000, (float) $kloterData['revenue']);
    }

    public function test_cash_payments_do_not_double_count_with_revenue(): void
    {
        // 1 jamaah kontrak 30.000.000
        $reg = $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $invoice = $reg->invoice;

        // Catat kas masuk pembayaran 10.000.000
        Payment::create([
            'invoice_id'         => $invoice->id,
            'transaction_number' => 'TRX-' . uniqid(),
            'receipt_number'     => 'REC-' . uniqid(),
            'amount'             => 10000000,
            'payment_date'       => now()->format('Y-m-d'),
            'payment_type'       => 'cicilan',
            'payment_method'     => 'transfer',
            'status'             => 'valid',
            'created_by'         => $this->admin->id,
        ]);

        $invoice->update([
            'paid_amount'      => 10000000,
            'remaining_amount' => 20000000,
            'status'           => 'sebagian',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'departure_id' => $this->kloter1->id,
        ]);

        $kloterData = $report['kloters']->get($this->kloter1->id);
        $this->assertEquals(30000000, (float) $kloterData['revenue'], 'Pendapatan tidak boleh double-counting dengan pembayaran kas masuk');
    }

    public function test_direct_expenses_only_hit_the_specific_departure(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter2, 30000000);

        // Biaya langsung untuk Kloter 1: 10.000.000
        $this->createExpense([
            'category_id'  => $this->catDirect->id,
            'departure_id' => $this->kloter1->id,
            'expense_type' => 'langsung',
            'title'        => 'Beban Tiket Kloter 1',
            'amount'       => 10000000,
            'expense_date' => '2026-03-01',
        ]);

        // Biaya langsung untuk Kloter 2: 5.000.000
        $this->createExpense([
            'category_id'  => $this->catDirect->id,
            'departure_id' => $this->kloter2->id,
            'expense_type' => 'langsung',
            'title'        => 'Beban Tiket Kloter 2',
            'amount'       => 5000000,
            'expense_date' => '2026-03-15',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss();

        $k1 = $report['kloters']->get($this->kloter1->id);
        $k2 = $report['kloters']->get($this->kloter2->id);

        $this->assertEquals(10000000, (float) $k1['direct_expense']);
        $this->assertEquals(5000000, (float) $k2['direct_expense']);
    }

    public function test_general_expenses_are_not_counted_as_direct_expenses(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        // Biaya operasional umum 12.000.000
        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => 'operasional_umum',
            'title'        => 'Sewa Kantor',
            'amount'       => 12000000,
            'expense_date' => '2026-03-01',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss();
        $k1 = $report['kloters']->get($this->kloter1->id);

        $this->assertEquals(0, (float) $k1['direct_expense']);
        $this->assertEquals(12000000, (float) $report['total_general_expense']);
    }

    public function test_profit_before_allocation_is_revenue_minus_direct_cost(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        $this->createExpense([
            'category_id'  => $this->catDirect->id,
            'departure_id' => $this->kloter1->id,
            'expense_type' => 'langsung',
            'title'        => 'Beban Hotel Kloter 1',
            'amount'       => 20000000,
            'expense_date' => '2026-03-01',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'departure_id' => $this->kloter1->id,
        ]);
        $k1 = $report['kloters']->get($this->kloter1->id);

        $this->assertEquals(60000000, (float) $k1['revenue']);
        $this->assertEquals(20000000, (float) $k1['direct_expense']);
        $this->assertEquals(40000000, (float) $k1['profit_before_allocation']);
        $this->assertEquals(66.67, $k1['margin_before_allocation']);
    }

    public function test_proportional_allocation_of_general_expense_based_on_active_jamaah(): void
    {
        // Kloter 1: 3 jamaah (60%)
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        // Kloter 2: 2 jamaah (40%)
        $this->createJamaahWithRegistration($this->kloter2, 30000000);
        $this->createJamaahWithRegistration($this->kloter2, 30000000);

        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => 'operasional_umum',
            'title'        => 'Gaji Staff Kantor',
            'amount'       => 10000000,
            'expense_date' => '2026-03-05',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'start_date' => '2026-03-01',
            'end_date'   => '2026-03-31',
        ]);

        $k1 = $report['kloters']->get($this->kloter1->id);
        $k2 = $report['kloters']->get($this->kloter2->id);

        $this->assertEquals(60.0, $k1['allocation_ratio']);
        $this->assertEquals(6000000, (float) $k1['allocated_general_expense']);

        $this->assertEquals(40.0, $k2['allocation_ratio']);
        $this->assertEquals(4000000, (float) $k2['allocated_general_expense']);

        $this->assertEquals(10000000, (float) $report['allocated_general_expense']);
    }

    public function test_profit_after_allocation_is_profit_before_minus_allocated_general(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createExpense([
            'category_id'  => $this->catDirect->id,
            'departure_id' => $this->kloter1->id,
            'expense_type' => 'langsung',
            'title'        => 'Beban Tiket',
            'amount'       => 10000000,
            'expense_date' => '2026-03-01',
        ]);

        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => 'operasional_umum',
            'title'        => 'Overhead Kantor',
            'amount'       => 6000000,
            'expense_date' => '2026-03-05',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'start_date' => '2026-03-01',
            'end_date'   => '2026-03-10',
        ]);

        $k1 = $report['kloters']->get($this->kloter1->id);

        $this->assertEquals(20000000, (float) $k1['profit_before_allocation']);
        $this->assertEquals(6000000, (float) $k1['allocated_general_expense']);
        $this->assertEquals(14000000, (float) $k1['profit_after_allocation']);
        $this->assertEquals(16000000, (float) $k1['total_expense_after']);
        $this->assertEquals('untung', $k1['status_profit']);
    }

    public function test_sum_of_allocated_general_expenses_equals_total_general_expenses(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter2, 30000000);
        $this->createJamaahWithRegistration($this->kloter3, 45000000);

        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => 'operasional_umum',
            'title'        => 'Biaya Listrik Kantor',
            'amount'       => 10000000,
            'expense_date' => '2026-03-01',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss();

        $sumAllocated = 0.0;
        foreach ($report['kloters'] as $item) {
            $sumAllocated += $item['allocated_general_expense'];
        }

        $this->assertEquals(10000000.0, round($sumAllocated, 2));
        $this->assertEquals(10000000.0, $report['allocated_general_expense']);
        $this->assertEquals(0.0, $report['unallocated_general_expense']);
    }

    public function test_zero_division_handled_safely_when_revenue_is_zero(): void
    {
        $this->createExpense([
            'category_id'  => $this->catDirect->id,
            'departure_id' => $this->kloter1->id,
            'expense_type' => 'langsung',
            'title'        => 'Biaya Operasional Tanpa Jamaah',
            'amount'       => 5000000,
            'expense_date' => '2026-03-01',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'departure_id' => $this->kloter1->id,
        ]);

        $k1 = $report['kloters']->get($this->kloter1->id);

        $this->assertEquals(0, (float) $k1['revenue']);
        $this->assertEquals(5000000, (float) $k1['direct_expense']);
        $this->assertEquals(-5000000, (float) $k1['profit_before_allocation']);
        $this->assertEquals(0, $k1['margin_before_allocation']);
        $this->assertEquals('rugi', $k1['status_profit']);
    }

    public function test_departure_with_zero_active_jamaah_gets_zero_allocation(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => 'operasional_umum',
            'title'        => 'Beban Kantor Umum',
            'amount'       => 8000000,
            'expense_date' => '2026-03-01',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'start_date' => '2026-03-01',
            'end_date'   => '2026-03-31',
        ]);

        $k1 = $report['kloters']->get($this->kloter1->id);
        $k2 = $report['kloters']->get($this->kloter2->id);

        $this->assertEquals(8000000, (float) $k1['allocated_general_expense']);
        $this->assertEquals(0.0, (float) $k2['allocated_general_expense']);
        $this->assertEquals(0.0, $k2['allocation_ratio']);
    }

    public function test_all_departures_with_zero_active_jamaah_does_not_throw_division_by_zero(): void
    {
        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => 'operasional_umum',
            'title'        => 'Beban Kantor Tanpa Jamaah',
            'amount'       => 5000000,
            'expense_date' => '2026-03-01',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss();

        $this->assertEquals(0, $report['total_active_jamaah']);
        $this->assertEquals(5000000, (float) $report['total_general_expense']);
        $this->assertEquals(0, (float) $report['allocated_general_expense']);
        $this->assertEquals(5000000, (float) $report['unallocated_general_expense']);
    }

    public function test_date_period_filter_filters_departures_and_expenses_correctly(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        $this->createJamaahWithRegistration($this->kloter3, 45000000);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'start_date' => '2026-03-01',
            'end_date'   => '2026-03-31',
        ]);

        $this->assertTrue($report['kloters']->has($this->kloter1->id));
        $this->assertFalse($report['kloters']->has($this->kloter3->id));
    }

    public function test_departure_filter_filters_single_departure_without_distorting_pool_denominator(): void
    {
        // Kloter 1: 1 jamaah (50% dari pool periode)
        $this->createJamaahWithRegistration($this->kloter1, 30000000);
        // Kloter 2: 1 jamaah (50% dari pool periode)
        $this->createJamaahWithRegistration($this->kloter2, 30000000);

        // Biaya operasional umum 10.000.000
        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => 'operasional_umum',
            'title'        => 'Biaya Listrik Kantor Pool',
            'amount'       => 10000000,
            'expense_date' => '2026-03-01',
        ]);

        // Filter tampilan HANYA untuk Kloter 2
        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'start_date'   => '2026-03-01',
            'end_date'     => '2026-03-31',
            'departure_id' => $this->kloter2->id,
        ]);

        $this->assertCount(1, $report['kloters']);
        $this->assertTrue($report['kloters']->has($this->kloter2->id));

        $k2 = $report['kloters']->get($this->kloter2->id);
        // Rasio alokasi Kloter 2 HARUS tetap 50% (bukan terdistorsi jadi 100%)
        $this->assertEquals(50.0, $k2['allocation_ratio'], 'Filter 1 kloter tidak boleh mengubah denominator alokasi pool periode');
        $this->assertEquals(5000000, (float) $k2['allocated_general_expense']);
        $this->assertEquals(2, $report['total_period_active_jamaah']);
        $this->assertEquals(1, $report['total_active_jamaah']);
    }

    public function test_cancelled_registrations_are_excluded_from_revenue_and_active_jamaah_count(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000, 'terdaftar');
        $this->createJamaahWithRegistration($this->kloter1, 30000000, 'batal');

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'departure_id' => $this->kloter1->id,
        ]);

        $k1 = $report['kloters']->get($this->kloter1->id);
        $this->assertEquals(1, $k1['active_jamaah_count']);
        $this->assertEquals(2, $k1['total_registered_count']);
        $this->assertEquals(30000000, (float) $k1['revenue']);
    }

    public function test_legacy_expenses_with_null_departure_are_treated_as_general_expenses(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        $this->createExpense([
            'category_id'  => $this->catGeneral->id,
            'departure_id' => null,
            'expense_type' => null,
            'title'        => 'Biaya Legacy Tanpa Kloter',
            'amount'       => 4000000,
            'expense_date' => '2026-03-01',
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'start_date' => '2026-03-01',
            'end_date'   => '2026-03-31',
        ]);

        $this->assertEquals(4000000, (float) $report['total_general_expense']);
    }

    public function test_cancelled_expenses_are_excluded_from_profit_and_loss_calculations(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        $this->createExpense([
            'category_id'  => $this->catDirect->id,
            'departure_id' => $this->kloter1->id,
            'expense_type' => 'langsung',
            'title'        => 'Beban Dibatalkan',
            'amount'       => 50000000,
            'expense_date' => '2026-03-01',
            'is_cancelled' => true,
        ]);

        $report = ProfitLossService::calculateDeparturesProfitLoss([
            'departure_id' => $this->kloter1->id,
        ]);

        $k1 = $report['kloters']->get($this->kloter1->id);
        $this->assertEquals(0, (float) $k1['direct_expense']);
    }

    public function test_profit_loss_report_requires_report_view_permission(): void
    {
        $responseAuthorized = $this->actingAs($this->admin)->get(route('reports.profit-loss-departures'));
        $responseAuthorized->assertStatus(200);

        $responseUnauthorized = $this->actingAs($this->unauthorizedUser)->get(route('reports.profit-loss-departures'));
        $responseUnauthorized->assertStatus(403);
    }

    public function test_export_pdf_and_excel_for_departures_profit_loss_returns_successful_response(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        $responsePdf = $this->actingAs($this->admin)->get(route('reports.profit-loss-departures', ['export' => 'pdf']));
        $responsePdf->assertStatus(200);
        $this->assertEquals('application/pdf', $responsePdf->headers->get('content-type'));

        $responseExcel = $this->actingAs($this->admin)->get(route('reports.profit-loss-departures', ['export' => 'excel']));
        $responseExcel->assertStatus(200);
    }

    public function test_detail_view_and_pdf_export_for_single_departure_returns_successful_response(): void
    {
        $this->createJamaahWithRegistration($this->kloter1, 30000000);

        $responseDetail = $this->actingAs($this->admin)->get(route('reports.profit-loss-departures.show', $this->kloter1->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee($this->kloter1->code);
        $responseDetail->assertSee('BAGIAN A');
        $responseDetail->assertSee('BAGIAN B');

        $responseDetailPdf = $this->actingAs($this->admin)->get(route('reports.profit-loss-departures.show', [
            'departure' => $this->kloter1->id,
            'export'    => 'pdf',
        ]));
        $responseDetailPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $responseDetailPdf->headers->get('content-type'));
    }
}
