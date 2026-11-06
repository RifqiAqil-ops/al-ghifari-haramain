<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\DepartureChecklist;
use App\Models\Package;
use App\Models\User;
use Tests\TestCase;

class ModuleIntegrationTest extends TestCase
{
    protected User $superAdmin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::where('email', 'superadmin@alghifari.com')->first();
        $this->staff = User::where('email', 'staff@alghifari.com')->first();
    }

    public function test_packages_index_and_detail_can_be_viewed(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('packages.index'));
        $response->assertStatus(200);
        $response->assertSee('Katalog Paket');

        $createResponse = $this->actingAs($this->superAdmin)->get(route('packages.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Tambah Paket Baru');

        $pkg = Package::first();
        if ($pkg) {
            $showResponse = $this->actingAs($this->superAdmin)->get(route('packages.show', $pkg));
            $showResponse->assertStatus(200);
            $showResponse->assertSee($pkg->name);
        }
    }

    public function test_departure_manifest_and_calendar_can_be_viewed(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('departures.index'));
        $response->assertStatus(200);

        $calResponse = $this->actingAs($this->superAdmin)->get(route('departures.calendar'));
        $calResponse->assertStatus(200);
        $calResponse->assertSee('Kalender');

        $dep = Departure::first();
        if ($dep) {
            $showDep = $this->actingAs($this->superAdmin)->get(route('departures.show', $dep));
            $showDep->assertStatus(200);
            $showDep->assertSee($dep->code);

            // Manifest PDF Generation
            $pdfResponse = $this->actingAs($this->superAdmin)->get(route('departures.manifest-pdf', $dep));
            $pdfResponse->assertStatus(200);
            $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        }
    }

    public function test_departure_checklist_can_be_updated(): void
    {
        $checklist = DepartureChecklist::first();
        if ($checklist) {
            $newStatus = !$checklist->is_completed;
            $response = $this->actingAs($this->superAdmin)->patch(route('departures.checklist', $checklist), [
                'is_completed' => $newStatus,
            ]);

            $response->assertStatus(302);
            $checklist->refresh();
            $this->assertEquals($newStatus, (bool) $checklist->is_completed);
        }
    }

    public function test_reports_can_be_rendered_and_exported_as_pdf(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Laporan');

        // Test payment PDF & Excel export
        $payPdf = $this->actingAs($this->superAdmin)->get(route('reports.payments', ['export' => 'pdf']));
        $payPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $payPdf->headers->get('Content-Type'));

        $payXls = $this->actingAs($this->superAdmin)->get(route('reports.payments', ['export' => 'excel']));
        $payXls->assertStatus(200);

        // Test receivables PDF & Excel export
        $recPdf = $this->actingAs($this->superAdmin)->get(route('reports.receivables', ['export' => 'pdf']));
        $recPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $recPdf->headers->get('Content-Type'));

        $recXls = $this->actingAs($this->superAdmin)->get(route('reports.receivables', ['export' => 'excel']));
        $recXls->assertStatus(200);

        // Test profit & loss PDF & Excel export
        $plPdf = $this->actingAs($this->superAdmin)->get(route('reports.profit-loss', ['export' => 'pdf']));
        $plPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $plPdf->headers->get('Content-Type'));

        $plXls = $this->actingAs($this->superAdmin)->get(route('reports.profit-loss', ['export' => 'excel']));
        $plXls->assertStatus(200);

        // Test departures PDF & Excel export
        $depPdf = $this->actingAs($this->superAdmin)->get(route('reports.departures', ['export' => 'pdf']));
        $depPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $depPdf->headers->get('Content-Type'));

        $depXls = $this->actingAs($this->superAdmin)->get(route('reports.departures', ['export' => 'excel']));
        $depXls->assertStatus(200);

        // Test jamaah PDF & Excel export
        $jamPdf = $this->actingAs($this->superAdmin)->get(route('jamaahs.export-pdf'));
        $jamPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $jamPdf->headers->get('Content-Type'));

        $jamXls = $this->actingAs($this->superAdmin)->get(route('jamaahs.export-excel'));
        $jamXls->assertStatus(200);
    }

    public function test_settings_and_audit_logs_can_be_viewed(): void
    {
        $setResponse = $this->actingAs($this->superAdmin)->get(route('settings.index'));
        $setResponse->assertStatus(200);
        $setResponse->assertSee('Al Ghifari Haramain');

        $logResponse = $this->actingAs($this->superAdmin)->get(route('audit-logs.index'));
        $logResponse->assertStatus(200);
        $logResponse->assertSee('Audit Log');
    }

    public function test_notifications_can_be_marked_as_read(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('notifications.index'));
        $response->assertStatus(200);

        $readAllResponse = $this->actingAs($this->superAdmin)->post(route('notifications.read-all'));
        $readAllResponse->assertStatus(302);
    }
}
