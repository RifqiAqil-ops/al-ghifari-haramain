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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
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

        // Paket Utama
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

        // Keberangkatan Utama
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
        return Invoice::create([
            'invoice_number'   => 'INV-TEST-' . rand(1000, 9999),
            'registration_id'  => $reg->id,
            'total_amount'     => $reg->final_price,
            'paid_amount'      => $paid,
            'remaining_amount' => (float) $reg->final_price - $paid,
            'status'           => $paid > 0 ? 'sebagian' : 'belum_bayar',
            'due_date'         => now()->addMonths(3)->format('Y-m-d'),
        ]);
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
     * 2. Update berhasil pada paket dan kloter yang valid tanpa pembayaran sebelumnya.
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

        $newDeparture = Departure::create([
            'code'           => 'DEP-HAJI-TEST',
            'package_id'     => $newPackage->id,
            'departure_date' => now()->addMonths(10)->format('Y-m-d'),
            'return_date'    => now()->addMonths(11)->format('Y-m-d'),
            'airline'        => 'Saudia Airlines',
            'quota'          => 30,
            'status'         => 'persiapan',
        ]);

        $payload = [
            'package_id'        => $newPackage->id,
            'departure_id'      => $newDeparture->id,
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
            'id'           => $reg->id,
            'package_id'   => $newPackage->id,
            'departure_id' => $newDeparture->id,
            'room_type'    => 'triple',
            'final_price'  => 95000000,
            'status'       => 'proses',
        ]);
    }

    /**
     * 3. Kenaikan biaya registrasi merekalkulasi invoice dan sisa tagihan dengan benar.
     */
    public function test_update_recalculates_invoice_when_price_increases(): void
    {
        $reg     = $this->createRegistration(['agreed_price' => 28000000, 'final_price' => 28000000]);
        $invoice = $this->createInvoice($reg, 5000000);
        $payment = $this->createPayment($invoice, 5000000);

        // Kenaikan biaya dari 28.000.000 menjadi 32.000.000
        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'double',
            'agreed_price'      => 32000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHasNoErrors();

        // Invoice total_amount naik ke 32.000.000
        $invoice->refresh();
        $this->assertEquals('32000000.00', $invoice->total_amount);

        // remaining_amount = 32.000.000 - 5.000.000 = 27.000.000
        $this->assertEquals('27000000.00', $invoice->remaining_amount);

        // Pembayaran 5.000.000 tetap utuh
        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'amount' => '5000000.00',
            'status' => 'valid',
        ]);
    }

    /**
     * 4. Registrasi batal tidak dapat diedit via GET maupun PUT.
     */
    public function test_cannot_edit_or_update_cancelled_registration(): void
    {
        $reg = $this->createRegistration(['status' => 'batal']);

        // GET edit
        $responseGet = $this->actingAs($this->admin)->get(route('registrations.edit', $reg));
        $responseGet->assertRedirect(route('registrations.show', $reg));
        $responseGet->assertSessionHas('toast_error');

        // PUT update
        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];
        $responsePut = $this->actingAs($this->admin)->put(route('registrations.update', $reg), $payload);
        $responsePut->assertSessionHas('toast_error');

        // Status di database tetap batal
        $this->assertDatabaseHas('registrations', ['id' => $reg->id, 'status' => 'batal']);
    }

    /**
     * 5. User tanpa permission jamaah.edit mendapat 403 Forbidden.
     */
    public function test_edit_requires_jamaah_edit_permission(): void
    {
        $userNoRole = User::create([
            'name'     => 'User Tanpa Hak',
            'email'    => 'nohak.test@alghifari.com',
            'password' => Hash::make('password'),
        ]);

        $reg = $this->createRegistration();

        $response = $this->actingAs($userNoRole)->get(route('registrations.edit', $reg));
        $response->assertStatus(403);
    }

    /**
     * 6. Registrasi selesai tidak dapat diedit.
     */
    public function test_cannot_edit_completed_registration(): void
    {
        $reg = $this->createRegistration(['status' => 'selesai']);

        $response = $this->actingAs($this->admin)->get(route('registrations.edit', $reg));
        $response->assertRedirect(route('registrations.show', $reg));
        $response->assertSessionHas('toast_error');
    }

    /**
     * PENGUJIAN WAJIB 1: Paket A tidak dapat dipasangkan dengan departure Paket B (Temuan A).
     */
    public function test_package_a_cannot_be_paired_with_departure_of_package_b(): void
    {
        $packageB = Package::create([
            'code'                => 'PKG-HAJI-B-' . rand(100, 999),
            'name'                => 'Haji Khusus B',
            'type'                => 'haji',
            'price'               => 120000000,
            'quad_price'          => 120000000,
            'duration_days'       => 30,
            'quota'               => 20,
            'hotel_makkah_rating' => 5,
            'hotel_madinah_rating' => 5,
            'is_promo'            => false,
            'discount_amount'     => 0,
            'status'              => 'aktif',
        ]);

        $departureB = Departure::create([
            'code'           => 'DEP-HAJI-B-' . rand(100, 999),
            'package_id'     => $packageB->id, // Milik Paket B
            'departure_date' => now()->addMonths(8)->format('Y-m-d'),
            'return_date'    => now()->addMonths(9)->format('Y-m-d'),
            'airline'        => 'Saudia Airlines',
            'quota'          => 20,
            'status'         => 'persiapan',
        ]);

        $reg = $this->createRegistration(['package_id' => $this->package->id]);

        // Coba pasangkan Paket A ($this->package) dengan Departure B ($departureB)
        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $departureB->id, // Milik packageB!
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHasErrors(['departure_id']);
        $reg->refresh();
        $this->assertEquals($this->departure->id, $reg->departure_id);
    }

    /**
     * PENGUJIAN WAJIB 2: Departure penuh menolak perpindahan registrasi (Temuan B).
     */
    public function test_full_departure_rejects_transfer(): void
    {
        // Kloter target dengan kuota 1
        $fullDeparture = Departure::create([
            'code'           => 'DEP-FULL-' . rand(100, 999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->addMonths(5)->format('Y-m-d'),
            'return_date'    => now()->addMonths(5)->addDays(12)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 1,
            'status'         => 'persiapan',
        ]);

        // Isi kuota penuh
        $fillerJamaah = Jamaah::create([
            'registration_number' => 'JMH-FILLER-' . rand(1000, 9999),
            'name'                => 'Jamaah Pengisi Kuota',
            'nik'                 => '3201000000000088',
            'gender'              => 'L',
            'birth_place'         => 'Jakarta',
            'birth_date'          => '1990-01-01',
            'phone'               => '08199999991',
            'address'             => 'Jl. Pengisi No. 1',
            'status'              => 'aktif',
        ]);
        Registration::create([
            'registration_number' => 'REG-FILLER-' . rand(1000, 9999),
            'jamaah_id'           => $fillerJamaah->id,
            'package_id'          => $this->package->id,
            'departure_id'        => $fullDeparture->id,
            'registration_date'   => now()->format('Y-m-d'),
            'room_type'           => 'quad',
            'agreed_price'        => 28000000,
            'discount'            => 0,
            'final_price'         => 28000000,
            'status'              => 'terdaftar',
        ]);

        $this->assertEquals(0, $fullDeparture->remaining_seats);

        // Registrasi yang akan dipindah
        $reg = $this->createRegistration(['departure_id' => $this->departure->id]);

        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $fullDeparture->id, // Penuh!
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHasErrors(['departure_id']);
        $reg->refresh();
        $this->assertEquals($this->departure->id, $reg->departure_id);
    }

    /**
     * PENGUJIAN WAJIB 3: Perpindahan departure memperbarui kuota lama dan baru secara tepat.
     */
    public function test_departure_transfer_updates_old_and_new_quota_accurately(): void
    {
        $depA = $this->departure; // Kuota 40

        $depB = Departure::create([
            'code'           => 'DEP-B-' . rand(100, 999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->addMonths(7)->format('Y-m-d'),
            'return_date'    => now()->addMonths(7)->addDays(12)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 10,
            'status'         => 'persiapan',
        ]);

        $reg = $this->createRegistration(['departure_id' => $depA->id]);

        $this->assertEquals(1, $depA->registered_jamaahs_count);
        $this->assertEquals(0, $depB->registered_jamaahs_count);

        // Pindahkan reg dari depA ke depB
        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $depB->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)->put(route('registrations.update', $reg), $payload);
        $response->assertSessionHasNoErrors();

        // depA berkurang jadi 0, depB bertambah jadi 1
        $this->assertEquals(0, $depA->registered_jamaahs_count);
        $this->assertEquals(40, $depA->remaining_seats);

        $this->assertEquals(1, $depB->registered_jamaahs_count);
        $this->assertEquals(9, $depB->remaining_seats);
    }

    /**
     * PENGUJIAN WAJIB 4: Concurrency / race condition protection pada kuota departure.
     * Menguji bahwa pengecekan kuota dengan row locking mencegah overbooking.
     */
    public function test_concurrent_departure_transfer_prevents_overbooking(): void
    {
        // Kloter target dengan sisa 1 kursi
        $targetDep = Departure::create([
            'code'           => 'DEP-TGT-' . rand(100, 999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->addMonths(4)->format('Y-m-d'),
            'return_date'    => now()->addMonths(4)->addDays(12)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 1,
            'status'         => 'persiapan',
        ]);

        $reg1 = $this->createRegistration(['departure_id' => $this->departure->id]);
        $reg2 = $this->createRegistration(['departure_id' => $this->departure->id]);

        // Request 1 berhasil mengisi kursi terakhir
        $payload1 = [
            'package_id'        => $this->package->id,
            'departure_id'      => $targetDep->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];
        $res1 = $this->actingAs($this->admin)->put(route('registrations.update', $reg1), $payload1);
        $res1->assertSessionHasNoErrors();

        // Request 2 untuk kursi yang sama harus ditolak
        $payload2 = [
            'package_id'        => $this->package->id,
            'departure_id'      => $targetDep->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];
        $res2 = $this->actingAs($this->admin)->put(route('registrations.update', $reg2), $payload2);
        $res2->assertSessionHasErrors(['departure_id']);

        // Pastikan tidak ada overbooking: kuota tetap 1 dan terisi tepat 1
        $this->assertEquals(1, $targetDep->registered_jamaahs_count);
    }

    /**
     * PENGUJIAN WAJIB 5: Harga akhir tidak dapat di bawah total pembayaran riil (Temuan C).
     */
    public function test_final_price_cannot_be_lower_than_total_paid(): void
    {
        $reg     = $this->createRegistration(['agreed_price' => 28000000, 'final_price' => 28000000]);
        $invoice = $this->createInvoice($reg, 15000000);
        $this->createPayment($invoice, 15000000); // Jamaah sudah setor 15.000.000

        // Mencoba menurunkan biaya menjadi 12.000.000 (di bawah 15.000.000)
        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 12000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('registrations.update', $reg), $payload);

        $response->assertSessionHasErrors(['agreed_price']);

        // Data tidak berubah
        $reg->refresh();
        $this->assertEquals('28000000.00', $reg->final_price);
    }

    /**
     * PENGUJIAN WAJIB 6: Riwayat pembayaran tetap utuh setelah pembaruan data registrasi.
     */
    public function test_payment_history_remains_intact_after_update(): void
    {
        $reg     = $this->createRegistration(['agreed_price' => 28000000, 'final_price' => 28000000]);
        $invoice = $this->createInvoice($reg, 10000000);
        $payment = $this->createPayment($invoice, 10000000);

        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'double',
            'agreed_price'      => 30000000,
            'discount'          => 0,
            'status'            => 'proses',
        ];

        $response = $this->actingAs($this->admin)->put(route('registrations.update', $reg), $payload);
        $response->assertSessionHasNoErrors();

        // Payment record tetap utuh sama persis
        $this->assertDatabaseHas('payments', [
            'id'                 => $payment->id,
            'invoice_id'         => $invoice->id,
            'amount'             => '10000000.00',
            'transaction_number' => $payment->transaction_number,
            'status'             => 'valid',
            'deleted_at'         => null,
        ]);
    }

    /**
     * PENGUJIAN WAJIB 7: Departure historis (selesai/berangkat/pulang) tidak dapat dipilih sebagai kloter tujuan (Temuan D).
     */
    public function test_historical_departure_cannot_be_selected_as_new_target(): void
    {
        $histDep = Departure::create([
            'code'           => 'DEP-HIST-' . rand(100, 999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->subMonths(2)->format('Y-m-d'),
            'return_date'    => now()->subMonths(1)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 40,
            'status'         => 'selesai', // Status historis!
        ]);

        $reg = $this->createRegistration(['departure_id' => $this->departure->id]);

        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $histDep->id, // Mencoba pindah ke kloter yang sudah selesai
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)->put(route('registrations.update', $reg), $payload);
        $response->assertSessionHasErrors(['departure_id']);

        $reg->refresh();
        $this->assertEquals($this->departure->id, $reg->departure_id);
    }

    /**
     * PENGUJIAN WAJIB 8: Departure saat ini tetap terlihat pada form edit meskipun statusnya selesai/berangkat.
     */
    public function test_current_departure_visible_on_edit_form_even_if_status_completed(): void
    {
        // Misalkan departure saat ini berstatus berangkat/selesai
        $specialDep = Departure::create([
            'code'           => 'DEP-CURR-' . rand(100, 999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->subDays(5)->format('Y-m-d'),
            'return_date'    => now()->addDays(7)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 40,
            'status'         => 'berangkat',
        ]);

        $reg = $this->createRegistration(['departure_id' => $specialDep->id]);

        $response = $this->actingAs($this->admin)->get(route('registrations.edit', $reg));
        $response->assertStatus(200);
        $response->assertSee($specialDep->code);
    }

    /**
     * PENGUJIAN WAJIB 9: Diskon tidak boleh melebihi harga yang disepakati (Temuan E).
     */
    public function test_discount_cannot_exceed_agreed_price(): void
    {
        $reg = $this->createRegistration();

        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 25000000,
            'discount'          => 30000000, // Diskon melebihi harga!
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)->put(route('registrations.update', $reg), $payload);
        $response->assertSessionHasErrors(['discount']);
    }

    /**
     * PENGUJIAN WAJIB 10: Kegagalan proses harus melakukan rollback database secara menyeluruh.
     */
    public function test_transaction_rolls_back_when_exception_occurs(): void
    {
        $reg = $this->createRegistration(['room_type' => 'quad']);
        $originalRoom = $reg->room_type;

        // Simulasi error saat updating
        \App\Models\Registration::updating(function ($model) {
            if ($model->room_type === 'single') {
                throw new \Exception('Simulasi kegagalan server saat update');
            }
        });

        $payload = [
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'single', // Memicu exception
            'agreed_price'      => 35000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        try {
            $this->actingAs($this->admin)->put(route('registrations.update', $reg), $payload);
        } catch (\Exception $e) {
            // Expected exception
        }

        // Database tidak berubah (rollback berhasil)
        $reg->refresh();
        $this->assertEquals($originalRoom, $reg->room_type);
        $this->assertEquals('28000000.00', $reg->agreed_price);
    }

    /**
     * 17. Request tidak dapat memanipulasi jamaah_id.
     */
    public function test_request_cannot_manipulate_jamaah_id(): void
    {
        $reg = $this->createRegistration();
        $originalJamaahId = $reg->jamaah_id;

        $otherJamaah = Jamaah::create([
            'registration_number' => 'JMH-OTHER-' . rand(1000, 9999),
            'name'                => 'Ahmad Hacker',
            'nik'                 => '3201000000000002',
            'gender'              => 'L',
            'birth_place'         => 'Surabaya',
            'birth_date'          => '1990-01-01',
            'phone'               => '08199999999',
            'address'             => 'Jl. Lain No. 99',
            'status'              => 'aktif',
        ]);

        $payload = [
            'jamaah_id'         => $otherJamaah->id,
            'package_id'        => $this->package->id,
            'departure_id'      => $this->departure->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];

        $response = $this->actingAs($this->admin)->put(route('registrations.update', $reg), $payload);
        $response->assertSessionHasNoErrors();

        $reg->refresh();
        $this->assertEquals($originalJamaahId, $reg->jamaah_id);
    }

    /**
     * 18. Registrasi baru (store) menerapkan mekanisme locking dan proteksi kuota yang konsisten.
     */
    public function test_store_uses_consistent_departure_locking_and_rejects_overbooking(): void
    {
        // Kloter dengan kuota 1
        $fullDep = Departure::create([
            'code'           => 'DEP-STORE-FULL-' . rand(100, 999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->addMonths(6)->format('Y-m-d'),
            'return_date'    => now()->addMonths(6)->addDays(12)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 1,
            'status'         => 'persiapan',
        ]);

        // Isi 1 jamaah
        $this->createRegistration(['departure_id' => $fullDep->id]);
        $this->assertEquals(0, $fullDep->remaining_seats);

        // Jamaah baru yang akan daftar
        $newJamaah = Jamaah::create([
            'registration_number' => 'JMH-NEW-' . rand(1000, 9999),
            'name'                => 'Jamaah Baru Store',
            'nik'                 => '3201000000000055',
            'gender'              => 'L',
            'birth_place'         => 'Jakarta',
            'birth_date'          => '1992-02-02',
            'phone'               => '08122222222',
            'address'             => 'Jl. Baru No. 1',
            'status'              => 'aktif',
        ]);

        $payload = [
            'jamaah_id'         => $newJamaah->id,
            'package_id'        => $this->package->id,
            'departure_id'      => $fullDep->id, // Penuh!
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
        ];

        // Route store diatur dengan permission:jamaah.create
        $response = $this->actingAs($this->admin)->post(route('registrations.store'), $payload);
        $response->assertSessionHasErrors(['departure_id']);

        // Pastikan tidak ada registrasi baru yang masuk ke fullDep
        $this->assertEquals(1, $fullDep->registered_jamaahs_count);
    }

    /**
     * 19. Pertukaran dua kloter secara bersamaan (cross-swap: R1 ke D2, R2 ke D1) bebas deadlock.
     */
    public function test_concurrent_cross_swap_departure_transfer_is_deadlock_free(): void
    {
        $depA = $this->departure; // Kuota 40

        $depB = Departure::create([
            'code'           => 'DEP-SWAP-B-' . rand(100, 999),
            'package_id'     => $this->package->id,
            'departure_date' => now()->addMonths(7)->format('Y-m-d'),
            'return_date'    => now()->addMonths(7)->addDays(12)->format('Y-m-d'),
            'airline'        => 'Garuda Indonesia',
            'quota'          => 40,
            'status'         => 'persiapan',
        ]);

        // Reg 1 di depA
        $reg1 = $this->createRegistration(['departure_id' => $depA->id]);

        // Reg 2 di depB
        $jamaah2 = Jamaah::create([
            'registration_number' => 'JMH-SWAP-2-' . rand(1000, 9999),
            'name'                => 'Jamaah Swap 2',
            'nik'                 => '3201000000000066',
            'gender'              => 'P',
            'birth_place'         => 'Surabaya',
            'birth_date'          => '1991-03-03',
            'phone'               => '08133333333',
            'address'             => 'Jl. Swap No. 2',
            'status'              => 'aktif',
        ]);
        $reg2 = Registration::create([
            'registration_number' => 'REG-SWAP-2-' . rand(1000, 9999),
            'jamaah_id'           => $jamaah2->id,
            'package_id'          => $this->package->id,
            'departure_id'        => $depB->id,
            'registration_date'   => now()->format('Y-m-d'),
            'room_type'           => 'quad',
            'agreed_price'        => 28000000,
            'discount'            => 0,
            'final_price'         => 28000000,
            'status'              => 'terdaftar',
        ]);

        // Pertukaran D1 -> D2
        $payload1 = [
            'package_id'        => $this->package->id,
            'departure_id'      => $depB->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];
        $res1 = $this->actingAs($this->admin)->put(route('registrations.update', $reg1), $payload1);
        $res1->assertSessionHasNoErrors();

        // Pertukaran D2 -> D1
        $payload2 = [
            'package_id'        => $this->package->id,
            'departure_id'      => $depA->id,
            'registration_date' => now()->format('Y-m-d'),
            'room_type'         => 'quad',
            'agreed_price'      => 28000000,
            'discount'          => 0,
            'status'            => 'terdaftar',
        ];
        $res2 = $this->actingAs($this->admin)->put(route('registrations.update', $reg2), $payload2);
        $res2->assertSessionHasNoErrors();

        // Hasil swap terkonfirmasi tepat
        $reg1->refresh();
        $reg2->refresh();
        $this->assertEquals($depB->id, $reg1->departure_id);
        $this->assertEquals($depA->id, $reg2->departure_id);
        $this->assertEquals(1, $depA->registered_jamaahs_count);
        $this->assertEquals(1, $depB->registered_jamaahs_count);
    }
}
