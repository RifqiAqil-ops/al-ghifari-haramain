<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\HotelCustomer;
use App\Models\HotelInvoice;
use App\Models\HotelPayment;
use App\Models\HotelReservation;
use App\Models\HotelRoomType;
use App\Models\User;
use App\Services\Hotel\Contracts\ExchangeRateProviderInterface;
use App\Services\Hotel\ExchangeRateService;
use App\Services\Hotel\HotelFinancialService;
use App\Services\Hotel\HotelInvoiceService;
use App\Services\Hotel\HotelPaymentService;
use App\Services\Hotel\HotelReservationService;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelBusinessManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Hotel $hotelMakkah;
    protected HotelRoomType $roomDouble;
    protected HotelCustomer $customer;
    protected ExchangeRateService $fxService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(UserSeeder::class);

        $this->superAdmin = User::where('email', 'superadmin@alghifari.com')->firstOrFail();

        // Seed Hotel Master
        $this->hotelMakkah = Hotel::create([
            'code' => 'HTL-MAK-001',
            'name' => 'Hotel Syahadah Makkah',
            'city' => 'Makkah',
            'country' => 'Saudi Arabia',
            'category' => 'Bintang 5',
            'star_rating' => 5,
        ]);

        $this->roomDouble = $this->hotelMakkah->roomTypes()->create([
            'name' => 'Double Room',
            'max_occupancy' => 2,
            'default_purchase_rate' => 800.00,
            'default_selling_rate' => 950.00,
            'currency' => 'SAR',
        ]);

        $this->customer = HotelCustomer::create([
            'customer_code' => 'CUST-HTL-0001',
            'name' => 'Syahadah Travel Umrah',
            'company_name' => 'PT Syahadah Mandiri',
            'customer_type' => 'travel_agency',
            'payment_terms_days' => 14,
        ]);

        // Mock Exchange Rate Provider returning deterministic rate (e.g. 1 SAR = Rp 4200.00)
        $mockProvider = new class implements ExchangeRateProviderInterface {
            public function getProviderName(): string { return 'MockProvider'; }
            public function isAvailable(): bool { return true; }
            public function getRate(string $baseCurrency = 'SAR', string $targetCurrency = 'IDR'): array {
                return [
                    'rate' => 4200.0000,
                    'provider' => 'MockProvider',
                    'timestamp' => now()->toIso8601String(),
                    'status' => 'live',
                ];
            }
        };

        $this->fxService = new ExchangeRateService($mockProvider);
    }

    public function test_exchange_rate_service_converts_correctly(): void
    {
        $sarAmount = 1000.00;
        $idrAmount = $this->fxService->convertSarToIdr($sarAmount, 4200.00);
        $this->assertEquals(4200000.00, $idrAmount);

        $convertedBackSar = $this->fxService->convertIdrToSar(4200000.00, 4200.00);
        $this->assertEquals(1000.00, $convertedBackSar);
    }

    public function test_exchange_rate_manual_override_with_audit_trail(): void
    {
        $this->actingAs($this->superAdmin);

        $record = $this->fxService->manualOverride(4350.00, 'Kesepakatan kurs kontrak khusus');
        $this->assertEquals(4350.00, $record->rate);
        $this->assertTrue($record->is_manual_override);

        $latest = $this->fxService->getLatestRate('SAR', 'IDR');
        $this->assertEquals(4350.00, $latest['rate']);
        $this->assertTrue($latest['is_manual']);

        $this->assertDatabaseHas('hotel_audit_logs', [
            'action' => 'exchange_rate_override',
        ]);
    }

    public function test_create_reservation_computes_room_nights_and_amounts(): void
    {
        $reservationService = new HotelReservationService($this->fxService);

        $reservation = $reservationService->createReservation([
            'customer_id' => $this->customer->id,
            'business_model' => 'reseller',
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-06', // 5 nights
            'items' => [
                [
                    'hotel_id' => $this->hotelMakkah->id,
                    'room_type_id' => $this->roomDouble->id,
                    'check_in' => '2026-12-01',
                    'check_out' => '2026-12-06',
                    'room_quantity' => 2, // 2 rooms * 5 nights = 10 room nights
                    'purchase_rate_per_night_sar' => 800.00,
                    'selling_rate_per_night_sar' => 950.00,
                ]
            ],
        ]);

        $this->assertEquals(5, $reservation->total_nights);
        $this->assertEquals(2, $reservation->total_rooms);
        // Cost: 10 * 800 = 8000 SAR
        $this->assertEquals(8000.00, $reservation->total_purchase_cost_sar);
        // Selling: 10 * 950 = 9500 SAR
        $this->assertEquals(9500.00, $reservation->total_selling_price_sar);
        // Profit: 9500 - 8000 = 1500 SAR
        $this->assertEquals(1500.00, $reservation->gross_profit_sar);
    }

    public function test_invoice_generation_locks_exchange_rate_snapshot(): void
    {
        $reservationService = new HotelReservationService($this->fxService);
        $invoiceService = new HotelInvoiceService($this->fxService);

        $reservation = $reservationService->createReservation([
            'customer_id' => $this->customer->id,
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-06',
            'items' => [
                [
                    'hotel_id' => $this->hotelMakkah->id,
                    'room_type_id' => $this->roomDouble->id,
                    'room_quantity' => 1,
                    'purchase_rate_per_night_sar' => 800.00,
                    'selling_rate_per_night_sar' => 1000.00, // 5 nights * 1000 = 5000 SAR
                ]
            ],
        ]);

        $invoice = $invoiceService->createInvoiceFromReservation($reservation, [
            'exchange_rate' => 4200.00,
        ]);

        $this->assertEquals(5000.00, $invoice->total_amount_sar);
        $this->assertEquals(4200.00, $invoice->exchange_rate_snapshot);
        // 5000 * 4200 = 21,000,000 IDR
        $this->assertEquals(21000000.00, $invoice->total_amount_idr);
        $this->assertEquals('issued', $invoice->status);
    }

    public function test_mixed_currency_payment_and_fx_gain_loss(): void
    {
        $reservationService = new HotelReservationService($this->fxService);
        $invoiceService = new HotelInvoiceService($this->fxService);
        $paymentService = new HotelPaymentService($this->fxService);

        $reservation = $reservationService->createReservation([
            'customer_id' => $this->customer->id,
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-06',
            'items' => [
                [
                    'hotel_id' => $this->hotelMakkah->id,
                    'room_type_id' => $this->roomDouble->id,
                    'room_quantity' => 1,
                    'purchase_rate_per_night_sar' => 800.00,
                    'selling_rate_per_night_sar' => 1000.00, // 5000 SAR
                ]
            ],
        ]);

        // Invoice created with snapshot rate 4200
        $invoice = $invoiceService->createInvoiceFromReservation($reservation, [
            'exchange_rate' => 4200.00,
        ]);

        // 1. Partial payment in SAR (2000 SAR) with rate at 4250
        $payment1 = $paymentService->recordPayment($invoice, [
            'payment_currency' => 'SAR',
            'payment_amount' => 2000.00,
            'payment_exchange_rate' => 4250.00,
            'payment_type' => 'dp',
            'payment_method' => 'transfer',
        ]);

        $invoice->refresh();
        $this->assertEquals(2000.00, $invoice->paid_amount_sar);
        $this->assertEquals(3000.00, $invoice->remaining_amount_sar);
        $this->assertEquals('sebagian', $invoice->status);
        // FX Gain: 2000 * (4250 - 4200) = +100,000 IDR
        $this->assertEquals(100000.00, $payment1->fx_gain_loss_idr);

        // 2. Full settlement in IDR (paying remaining 3000 SAR at rate 4200 = 12,600,000 IDR)
        $payment2 = $paymentService->recordPayment($invoice, [
            'payment_currency' => 'IDR',
            'payment_amount' => 12600000.00,
            'payment_exchange_rate' => 4200.00,
            'payment_type' => 'pelunasan',
            'payment_method' => 'transfer',
        ]);

        $invoice->refresh();
        $this->assertEquals(5000.00, $invoice->paid_amount_sar);
        $this->assertEquals(0.00, $invoice->remaining_amount_sar);
        $this->assertEquals('lunas', $invoice->status);
    }

    public function test_hotel_financial_and_consolidated_reporting(): void
    {
        $financialService = new HotelFinancialService();
        $metrics = $financialService->getHotelFinancialSummary();
        $consolidated = $financialService->getConsolidatedCompanyMetrics();

        $this->assertArrayHasKey('total_sales_sar', $metrics);
        $this->assertArrayHasKey('cash_collected_sar', $metrics);
        $this->assertArrayHasKey('receivables_sar', $metrics);
        $this->assertArrayHasKey('haji_umrah', $consolidated);
        $this->assertArrayHasKey('hotel', $consolidated);
        $this->assertArrayHasKey('consolidated', $consolidated);
    }

    public function test_hotel_routes_accessibility_for_admin(): void
    {
        $this->actingAs($this->superAdmin);

        $responseDashboard = $this->get(route('hotel.dashboard'));
        $responseDashboard->assertStatus(200);

        $responseRates = $this->get(route('hotel.rates.index'));
        $responseRates->assertStatus(200);

        $responseMaster = $this->get(route('hotel.master.index'));
        $responseMaster->assertStatus(200);

        $responseInvoices = $this->get(route('hotel.invoices.index'));
        $responseInvoices->assertStatus(200);

        $responseReports = $this->get(route('hotel.reports.index'));
        $responseReports->assertStatus(200);
    }

    public function test_unauthorized_staff_without_permission_receives_forbidden(): void
    {
        $staffUser = User::where('email', 'staff@alghifari.com')->firstOrFail();
        $this->actingAs($staffUser);

        // Staff lacks hotel.view by default
        $response = $this->get(route('hotel.dashboard'));
        $response->assertStatus(403);

        $responseReports = $this->get(route('hotel.reports.index'));
        $responseReports->assertStatus(403);
    }

    public function test_ai_assistant_understands_hotel_intent(): void
    {
        $this->actingAs($this->superAdmin);

        $aiManager = new \App\Services\Intelligence\AiManager();
        $reply = $aiManager->chat('Berapa omzet penjualan hotel dalam SAR dan IDR saat ini?');

        $this->assertArrayHasKey('content', $reply);
        $this->assertStringContainsString('Hotel', $reply['content']);
        $this->assertStringContainsString('SAR', $reply['content']);
        $this->assertStringContainsString('Kurs Acuan', $reply['content']);
    }
}
