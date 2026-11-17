<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staffNoEdit;
    protected Jamaah $jamaah;
    protected Package $package;
    protected Departure $departure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        // Admin user (Super Admin = memiliki jamaah.edit)
        $this->admin = User::create([
            'name'     => 'Admin Test Edit Reg',
            'email'    => 'admin.edit.test@alghifari.com',
            'password' => Hash::make('password'),
        ]);
        $this->admin->assignRole('Super Admin');

        // Jamaah
        $this->jamaah = Jamaah::create([
            'registration_number' => 'JMH-TEST-' . rand(1000, 9999),
            'name'                => 'Siti Nurhaliza Test',
            'nik'                 => '3171000000000001',
            'gender'              => 'P',
            'birth_place'         => 'Bandung',
            'birth_date'          => '1985-01-01',
            'phone'               => '08123456789',
            'address'             => 'Jl. Test No. 1',
            'status'              => 'aktif',
        ]);

        // Paket
        $this->package = Package::create([
            'code'                => 'PKG-TEST-' . rand(1000, 9999),
            'name'                => 'Umrah Reguler Test',
            'type'                => 'umrah',
            'price'               => 28000000,
            'quad_price'          => 28000000,
            'triple_price'        => 30000000,
            'double_price'        => 32000000,
            'duration_days'       => 12,
            'quota'               => 40,
            'hotel_makkah_rating' => 3,
            'hotel_madinah_rating' => 3,
            'is_promo'            => false,
            'discount_amount'     => 0,
            'status'              => 'aktif',
            'description'         => 'Paket Umrah test',
        ]);

        // Keberangkatan
        $this->departure = Departure::create([
            'code'           => 'UMR-TEST-' . rand(1000, 9999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->addMonths(6)->format('Y-m-d'),
            'return_date'    => now()->addMonths(6)->addDays(12)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 40,
            'status'         => 'persiapan',
        ]);
    }

    // ─── Helper ──────────────────────────────────────────────────────────

    private function createRegistration(array $override = []): Registration
    {
        return Registration::create(array_merge([
            'registration_number' => 'REG-TEST-' . rand(1000, 9999),
            'jamaah_id'           => $this->jamaah->id,
            'package_id'          => $this->package->id,
            'departure_id'        => $this->departure->id,
            'registration_date'   => now()->format('Y-m-d'),
            'room_type'           => 'quad',
            'agreed_price'        => 28000000,
            'discount'            => 0,
            'final_price'         => 28000000,
            'status'              => 'terdaftar',
        ], $override));
    }

    private function createInvoice(Registration $reg, float $paid = 0): Invoice
    {
        $invoice = Invoice::create([
            'invoice_number'   => 'INV-TEST-' . rand(1000, 9999),
            'registration_id'  => $reg->id,
            'total_amount'     => $reg->final_price,
            'paid_amount'      => $paid,
            'remaining_amount' => (float) $reg->final_price - $paid,
            'status'           => $paid > 0 ? 'sebagian' : 'belum_bayar',
            'due_date'         => now()->addMonths(3)->format('Y-m-d'),
        ]);

        return $invoice;
    }

    private function createPayment(Invoice $invoice, float $amount): Payment
    {
        return Payment::create([
            'invoice_id'         => $invoice->id,
            'transaction_number' => 'TRX-TEST-' . rand(1000, 9999),
            'receipt_number'     => 'RCP-TEST-' . rand(1000, 9999),
            'payment_date'       => now()->format('Y-m-d'),
            'amount'             => $amount,
            'payment_type'       => 'dp',
            'payment_method'     => 'transfer',
            'status'             => 'valid',
            'received_by'        => $this->admin->id,
        ]);
    }

    // ─── Tests ───────────────────────────────────────────────────────────

    /**
     * 1. Halaman edit tampil 200 untuk registrasi aktif.
     */
    public function test_can_view_edit_form_for_active_registration(): void
    {
        $reg = $this->createRegistration();

        $response = $this->actingAs($this->admin)
            ->get(route('registrations.edit', $reg));

        $response->assertStatus(200);
        $response->assertSee($reg->registration_number);
        $response->assertSee('Edit Registrasi');
        $response->assertSee('Simpan Perubahan');
    }

    /**
     * 2. Update berhasil tanpa pembayaran sebelumnya.
     */
    public function test_can_update_registration_without_payment(): void
    {
        $reg = $this->createRegistration();

        $newPackage = Package::create([
            'code'                => 'PKG-HAJI-TEST',
            'name'                => 'Haji Plus Test',
            'type'                => 'haji',
            'price'               => 95000000,
            'quad_price'          => 95000000,
            'duration_days'       => 40,
            'quota'               => 40,
            'hotel_makkah_rating' => 5,
            'hotel_madinah_rating' => 5,
            'is_promo'            => false,
            'discount_amount'     => 0,
            'status'              => 'aktif',
            'description'         => 'Paket Haji test',
        ]);

        $payload = [
            'package_id'        => $newPackage->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'triple',
            'agreed_price'      => 95000000,
            'discount'          => 0,
            'status'            => 'proses',
            'special_notes'     => 'Catatan updated',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('registrations.show', $reg));

        $this->assertDatabaseHas('registrations', [
            'id'          => $reg->id,
            'package_id'  => $newPackage->id,
            'room_type'   => 'triple',
            'final_price' => 95000000,
            'status'      => 'proses',
        ]);
    }

    /**
     * 3. Perubahan biaya merekalkulasi invoice tanpa menghapus pembayaran.
     */
    public function test_update_recalculates_invoice_when_price_changes(): void
    {
        $reg     = $this->createRegistration(['agreed_price' => 28000000, 'final_price' => 28000000]);
        $invoice = $this->createInvoice($reg, 5000000);
        $payment = $this->createPayment($invoice, 5000000);

        // Ubah biaya menjadi 30.000.000
        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 30000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHasNoErrors();

        // Invoice total_amount harus berubah ke 30.000.000
        $invoice->refresh();
        $this->assertEquals('30000000.00', $invoice->total_amount);

        // remaining_amount = 30.000.000 - 5.000.000 = 25.000.000
        $this->assertEquals('25000000.00', $invoice->remaining_amount);

        // Pembayaran asli tetap ada dan tidak diubah
        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'amount' => '5000000.00',
            'status' => 'valid',
        ]);
    }

    /**
     * 4. Registrasi batal → redirect, tidak boleh diedit.
     */
    public function test_cannot_edit_cancelled_registration(): void
    {
        $reg = $this->createRegistration(['status' => 'batal']);

        $response = $this->actingAs($this->admin)
            ->get(route('registrations.edit', $reg));

        $response->assertRedirect(route('registrations.show', $reg));
        $response->assertSessionHas('toast_error');
    }

    /**
     * 5. Update registrasi batal → ditolak.
     */
    public function test_cannot_update_cancelled_registration(): void
    {
        $reg = $this->createRegistration(['status' => 'batal']);

        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHas('toast_error');

        // Status di database tidak berubah
        $this->assertDatabaseHas('registrations', [
            'id'     => $reg->id,
            'status' => 'batal',
        ]);
    }

    /**
     * 6. User tanpa permission jamaah.edit mendapat 403.
     */
    public function test_edit_requires_jamaah_edit_permission(): void
    {
        // User tanpa role / permission apapun
        $userNoRole = User::create([
            'name'     => 'User Tanpa Role',
            'email'    => 'norole.test@alghifari.com',
            'password' => Hash::make('password'),
        ]);

        $reg = $this->createRegistration();

        $response = $this->actingAs($userNoRole)
            ->get(route('registrations.edit', $reg));

        $response->assertStatus(403);
    }

    /**
     * 7. Registrasi selesai → tidak dapat diedit.
     */
    public function test_cannot_edit_completed_registration(): void
    {
        $reg = $this->createRegistration(['status' => 'selesai']);

        $response = $this->actingAs($this->admin)
            ->get(route('registrations.edit', $reg));

        $response->assertRedirect(route('registrations.show', $reg));
        $response->assertSessionHas('toast_error');
    }

    /**
     * 8. Validasi gagal: package_id kosong → kembali dengan error.
     */
    public function test_update_validation_fails_when_package_id_missing(): void
    {
        $reg = $this->createRegistration();

        $payload = [
            'package_id'        => '',
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHasErrors(['package_id']);
    }
}
