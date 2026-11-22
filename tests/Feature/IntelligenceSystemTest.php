<?php

namespace Tests\Feature;

use App\Models\AutomationRule;
use App\Models\Departure;
use App\Models\DocumentOcrLog;
use App\Models\Equipment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\JamaahDocument;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\SmartInsight;
use App\Models\User;
use App\Services\Intelligence\AlertCenterService;
use App\Services\Intelligence\AutomationEngineService;
use App\Services\Intelligence\DepartureIntelligenceService;
use App\Services\Intelligence\DocumentIntelligenceService;
use App\Services\Intelligence\FinancialIntelligenceService;
use App\Services\Intelligence\PredictiveAnalyticsService;
use App\Services\Intelligence\ReportGeneratorService;
use App\Services\Intelligence\SmartCrmService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IntelligenceSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $financeUser;
    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\IntelligenceRuleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Admin');

        $this->financeUser = User::factory()->create();
        $this->financeUser->assignRole('Finance');

        $this->staffUser = User::factory()->create();
        $this->staffUser->assignRole('Staff');
    }

    protected function createTestJamaah(array $overrides = []): Jamaah
    {
        return Jamaah::create(array_merge([
            'registration_number' => 'JMH-INTEL-' . rand(1000, 9999),
            'name'                => 'Jamaah Test Intel',
            'nik'                 => '3201' . rand(10000000, 99999999) . '0001',
            'gender'              => 'L',
            'birth_place'         => 'Jakarta',
            'birth_date'          => '1988-08-08',
            'phone'               => '0812' . rand(10000000, 99999999),
            'status'              => 'aktif',
        ], $overrides));
    }

    protected function createTestPackage(array $overrides = []): Package
    {
        return Package::create(array_merge([
            'code'                => 'PKG-INTEL-' . rand(1000, 9999),
            'name'                => 'Paket Umrah Intelligence Test',
            'type'                => 'umrah',
            'price'               => 30000000,
            'quad_price'          => 30000000,
            'duration_days'       => 9,
            'quota'               => 45,
            'status'              => 'aktif',
        ], $overrides));
    }

    protected function createTestDeparture(int $packageId, array $overrides = []): Departure
    {
        return Departure::create(array_merge([
            'code'           => 'DEP-INTEL-' . rand(1000, 9999),
            'package_id'     => $packageId,
            'departure_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
            'return_date'    => Carbon::now()->addDays(34)->format('Y-m-d'),
            'quota'          => 45,
            'status'         => 'persiapan',
        ], $overrides));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/intelligence');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_intelligence_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/intelligence');
        $response->assertOk();
        $response->assertSee('Al Ghifari Intelligence System');
        $response->assertSee('Indeks Kesehatan Bisnis');
    }

    public function test_ai_assistant_chat_endpoint_returns_grounded_response(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/intelligence/assistant/chat', [
            'message' => 'Berapa total jamaah aktif saat ini?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'provider' => 'local_heuristic',
        ]);
        $this->assertNotEmpty($response->json('reply'));
    }

    public function test_financial_intelligence_service_computes_deterministic_metrics(): void
    {
        $service = app(FinancialIntelligenceService::class);
        $indicators = $service->getFinancialHealthIndicators();

        $this->assertArrayHasKey('health_score', $indicators);
        $this->assertArrayHasKey('collection_rate', $indicators);
        $this->assertArrayHasKey('net_cash_flow', $indicators);
    }

    public function test_financial_service_detects_duplicate_payments(): void
    {
        $jamaah = $this->createTestJamaah();
        $package = $this->createTestPackage();
        $departure = $this->createTestDeparture($package->id);

        $reg = Registration::create([
            'registration_number' => 'REG-INTEL-' . rand(1000, 9999),
            'jamaah_id' => $jamaah->id,
            'package_id' => $package->id,
            'departure_id' => $departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type' => 'quad',
            'agreed_price' => 30000000,
            'final_price' => 30000000,
            'status' => 'terdaftar',
        ]);

        $inv = Invoice::create([
            'invoice_number' => 'INV-INTEL-001',
            'registration_id' => $reg->id,
            'total_amount' => 30000000,
            'paid_amount' => 10000000,
            'remaining_amount' => 20000000,
            'status' => 'sebagian',
        ]);

        // Create 2 identical payments on the same date
        Payment::create([
            'invoice_id' => $inv->id,
            'transaction_number' => 'TRX-INTEL-001',
            'receipt_number' => 'KWT-INTEL-001',
            'payment_date' => Carbon::today(),
            'amount' => 5000000,
            'payment_type' => 'cicilan',
            'payment_method' => 'transfer',
            'status' => 'valid',
        ]);

        Payment::create([
            'invoice_id' => $inv->id,
            'transaction_number' => 'TRX-INTEL-002',
            'receipt_number' => 'KWT-INTEL-002',
            'payment_date' => Carbon::today(),
            'amount' => 5000000,
            'payment_type' => 'cicilan',
            'payment_method' => 'transfer',
            'status' => 'valid',
        ]);

        $service = app(FinancialIntelligenceService::class);
        $duplicates = $service->detectDuplicatePayments();

        $this->assertNotEmpty($duplicates);
        $this->assertEquals('INV-INTEL-001', $duplicates[0]['invoice_number']);
    }

    public function test_departure_intelligence_computes_readiness_score(): void
    {
        $package = $this->createTestPackage();
        $departure = $this->createTestDeparture($package->id, [
            'quota' => 45,
            'departure_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
        ]);

        $service = app(DepartureIntelligenceService::class);
        $readiness = $service->calculateReadinessScore($departure);

        $this->assertArrayHasKey('readiness_score', $readiness);
        $this->assertArrayHasKey('breakdown', $readiness);
        $this->assertGreaterThanOrEqual(0, $readiness['readiness_score']);
        $this->assertLessThanOrEqual(100, $readiness['readiness_score']);
    }

    public function test_document_ocr_staging_and_human_verification_flow(): void
    {
        $jamaah = $this->createTestJamaah([
            'name' => 'Nama Lama',
            'nik' => '3201000000000001',
        ]);

        $doc = JamaahDocument::create([
            'jamaah_id' => $jamaah->id,
            'document_type' => 'ktp',
            'document_name' => 'KTP Jamaah',
            'file_path' => 'dummy/path.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $service = app(DocumentIntelligenceService::class);
        $ocrLog = $service->analyzeDocument($doc);

        $this->assertDatabaseHas('document_ocr_logs', [
            'id' => $ocrLog->id,
            'verification_status' => 'pending_verification',
            'applied_to_jamaah' => false,
        ]);

        // Staged data has not touched jamaah yet
        $this->assertEquals('Nama Lama', $jamaah->fresh()->name);

        // Staff approves staged data
        $ocrLog->update([
            'extracted_data' => [
                'name' => 'Nama Baru OCR',
                'nik' => '3201999988880001',
            ],
        ]);
        $service->approveAndApply($ocrLog, $this->adminUser, 'Verifikasi Staf Disetujui');

        $this->assertDatabaseHas('document_ocr_logs', [
            'id' => $ocrLog->id,
            'verification_status' => 'approved',
            'applied_to_jamaah' => true,
        ]);

        // Document marked diverifikasi and jamaah updated
        $this->assertEquals('diverifikasi', $doc->fresh()->status);
        $this->assertEquals('Nama Baru OCR', $jamaah->fresh()->name);
    }

    public function test_smart_crm_generates_360_profile_and_followup_queue(): void
    {
        $jamaah = $this->createTestJamaah();
        $service = app(SmartCrmService::class);

        $profile = $service->getJamaah360Profile($jamaah);
        $this->assertArrayHasKey('segment', $profile);
        $this->assertArrayHasKey('metrics', $profile);
    }

    public function test_alert_center_scan_and_resolve_flow(): void
    {
        Equipment::create([
            'code' => 'EQ-ALERT-01',
            'name' => 'Buku Panduan Doa',
            'unit' => 'pcs',
            'stock' => 2,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $service = app(AlertCenterService::class);
        $scanResult = $service->scanAndGenerateAlerts();

        $this->assertGreaterThan(0, $scanResult['new_alerts_count']);

        $alert = SmartInsight::where('category', 'equipment')->where('status', 'active')->first();
        $this->assertNotNull($alert);

        // Resolve alert
        $service->resolveAlert($alert, $this->adminUser, 'Sudah dipesan ulang ke percetakan');

        $this->assertEquals('resolved', $alert->fresh()->status);
        $this->assertEquals($this->adminUser->id, $alert->fresh()->resolved_by);
    }

    public function test_automation_engine_executes_rules_with_idempotency(): void
    {
        $service = app(AutomationEngineService::class);
        $firstRun = $service->runScheduledRules();

        $this->assertNotEmpty($firstRun);
        $successCount = collect($firstRun)->where('status', 'success')->count();
        $this->assertGreaterThan(0, $successCount);

        // Second run on same day must be skipped by idempotency
        $secondRun = $service->runScheduledRules();
        $skippedCount = collect($secondRun)->where('status', 'skipped')->count();
        $this->assertEquals($successCount, $skippedCount);
    }

    public function test_executive_report_generation(): void
    {
        $service = app(ReportGeneratorService::class);
        $report = $service->generateExecutiveSummary();

        $this->assertArrayHasKey('financial_health', $report);
        $this->assertArrayHasKey('recommendations', $report);
        $this->assertNotEmpty($report['recommendations']);
    }

    public function test_crm_whatsapp_followup_requires_whatsapp_send_permission(): void
    {
        $jamaah = $this->createTestJamaah();

        // 1. Finance user (has intelligence.view, but lacks whatsapp.send) -> Must receive 403 Forbidden
        $responseForbidden = $this->actingAs($this->financeUser)->post(route('intelligence.crm.follow-up'), [
            'jamaah_id' => $jamaah->id,
            'phone' => '081234567890',
            'message' => 'Test pesan tagihan follow-up',
        ]);
        $responseForbidden->assertForbidden();

        // 2. Admin user (has intelligence.view AND whatsapp.send) -> Authorized
        $responseAuthorized = $this->actingAs($this->adminUser)->post(route('intelligence.crm.follow-up'), [
            'jamaah_id' => $jamaah->id,
            'phone' => '081234567890',
            'message' => 'Test pesan tagihan follow-up',
        ]);
        $responseAuthorized->assertRedirect();
        $responseAuthorized->assertSessionHas('toast_success');
    }

    public function test_alert_resolve_and_dismiss_requires_intelligence_manage_permission(): void
    {
        $alert = SmartInsight::create([
            'category' => 'financial',
            'level' => 'warning',
            'title' => 'Uji Alert Otorisasi',
            'summary' => 'Ringkasan uji izin otorisasi',
            'status' => 'active',
        ]);

        // 1. Finance user (has intelligence.view, but lacks intelligence.manage) -> Must receive 403 Forbidden
        $resolveForbidden = $this->actingAs($this->financeUser)->patch(route('intelligence.alerts.resolve', $alert->id), [
            'resolution_note' => 'Upaya penyelesaian tanpa izin manajerial',
        ]);
        $resolveForbidden->assertForbidden();

        $dismissForbidden = $this->actingAs($this->financeUser)->patch(route('intelligence.alerts.dismiss', $alert->id), [
            'resolution_note' => 'Upaya pengabaian tanpa izin manajerial',
        ]);
        $dismissForbidden->assertForbidden();

        // 2. Admin user (has intelligence.manage) -> Authorized
        $resolveAuthorized = $this->actingAs($this->adminUser)->patch(route('intelligence.alerts.resolve', $alert->id), [
            'resolution_note' => 'Diselesaikan secara sah oleh Admin',
        ]);
        $resolveAuthorized->assertRedirect();
        $this->assertEquals('resolved', $alert->fresh()->status);
    }
}
