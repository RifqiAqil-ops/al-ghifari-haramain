<?php

namespace Tests\Feature;

use App\Models\Departure;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDistribution;
use App\Models\EquipmentDistributionItem;
use App\Models\EquipmentMutation;
use App\Models\Jamaah;
use App\Models\Package;
use App\Models\User;
use Database\Seeders\EquipmentSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EquipmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Jalankan seeder roles & permissions
        $this->seed(RoleAndPermissionSeeder::class);

        // Buat user admin dengan role Super Admin
        $this->adminUser = User::create([
            'name' => 'Petugas Logistik Test',
            'email' => 'logistik.test@alghifari.com',
            'password' => Hash::make('password123'),
        ]);
        $this->adminUser->assignRole('Super Admin');
    }

    /**
     * Test dapat melihat halaman daftar inventaris perlengkapan.
     */
    public function test_can_view_equipment_inventory_index(): void
    {
        $category = EquipmentCategory::create([
            'name' => 'Koper & Tas Test',
            'slug' => 'koper-tas-test',
        ]);

        $item = Equipment::create([
            'category_id' => $category->id,
            'name' => 'Koper Bagasi 24 Inch',
            'code' => 'EQ-KPR-TEST',
            'unit' => 'unit',
            'stock' => 50,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('equipment.index'));

        $response->assertStatus(200);
        $response->assertSee('Koper Bagasi 24 Inch');
        $response->assertSee('EQ-KPR-TEST');
    }

    /**
     * Test dapat menambah master perlengkapan baru.
     */
    public function test_can_create_new_equipment(): void
    {
        $category = EquipmentCategory::create([
            'name' => 'Aksesoris Test',
            'slug' => 'aksesoris-test',
        ]);

        $postData = [
            'category_id' => $category->id,
            'name' => 'Syal Satin Hijau',
            'code' => 'EQ-SYL-TEST',
            'unit' => 'pcs',
            'initial_stock' => 100,
            'min_stock' => 15,
            'description' => 'Syal identitas jamaah',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('equipment.store'), $postData);

        $response->assertRedirect(route('equipment.index'));
        $this->assertDatabaseHas('equipments', [
            'code' => 'EQ-SYL-TEST',
            'name' => 'Syal Satin Hijau',
            'stock' => 100,
        ]);

        // Verifikasi mutasi awal tercatat
        $this->assertDatabaseHas('equipment_mutations', [
            'type' => 'in',
            'quantity' => 100,
            'stock_before' => 0,
            'stock_after' => 100,
        ]);
    }

    /**
     * Test dapat melakukan restock (stok masuk) dan catat mutasi.
     */
    public function test_can_restock_equipment_and_record_mutation(): void
    {
        $item = Equipment::create([
            'name' => 'Tas Selempang Paspor',
            'code' => 'EQ-TAS-TEST',
            'unit' => 'pcs',
            'stock' => 20,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('equipment.stock.mutate', $item), [
            'type' => 'in',
            'quantity' => 30,
            'notes' => 'Pengadaan gelombang 2 dari vendor',
        ]);

        $response->assertSessionHasNoErrors();
        $item->refresh();

        $this->assertEquals(50, $item->stock);
        $this->assertDatabaseHas('equipment_mutations', [
            'equipment_id' => $item->id,
            'type' => 'in',
            'quantity' => 30,
            'stock_before' => 20,
            'stock_after' => 50,
        ]);
    }

    /**
     * Test dapat mencatat barang rusak (stok berkurang).
     */
    public function test_can_record_damaged_stock(): void
    {
        $item = Equipment::create([
            'name' => 'Bantal Leher Busa',
            'code' => 'EQ-BTL-TEST',
            'unit' => 'pcs',
            'stock' => 40,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('equipment.stock.mutate', $item), [
            'type' => 'damaged',
            'quantity' => 3,
            'notes' => 'Robek saat pengiriman gudang',
        ]);

        $response->assertSessionHasNoErrors();
        $item->refresh();

        $this->assertEquals(37, $item->stock);
        $this->assertDatabaseHas('equipment_mutations', [
            'equipment_id' => $item->id,
            'type' => 'damaged',
            'quantity' => 3,
            'stock_before' => 40,
            'stock_after' => 37,
        ]);
    }

    /**
     * Test distribusi fleksibel ke jamaah individual dan otomatis memotong stok.
     */
    public function test_can_distribute_equipment_to_jamaah_and_deducts_stock(): void
    {
        $jamaah = Jamaah::create([
            'registration_number' => 'REG-TST-001',
            'name' => 'Ahmad Dahlan Test',
            'nik' => '3201019901010001',
            'gender' => 'L',
            'birth_place' => 'Bandung',
            'birth_date' => '1990-01-01',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 1',
            'status' => 'aktif',
        ]);

        $item1 = Equipment::create([
            'name' => 'Koper 24 Inch Test',
            'code' => 'EQ-KPR-01',
            'unit' => 'unit',
            'stock' => 25,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $item2 = Equipment::create([
            'name' => 'Kain Ihram Test',
            'code' => 'EQ-IHR-01',
            'unit' => 'set',
            'stock' => 50,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $distributionData = [
            'jamaah_id' => $jamaah->id,
            'distribution_date' => now()->format('Y-m-d'),
            'received_by' => 'Ahmad Dahlan Sendiri',
            'notes' => 'Pengambilan tahap 1 di kantor',
            'items' => [
                [
                    'equipment_id' => $item1->id,
                    'quantity' => 1,
                    'notes' => 'Warna Cokelat Gold',
                ],
                [
                    'equipment_id' => $item2->id,
                    'quantity' => 1,
                    'notes' => 'Standar Katun Premium',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('equipment.distributions.store'), $distributionData);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('equipment_distributions', [
            'jamaah_id' => $jamaah->id,
            'status' => 'diserahkan',
            'received_by' => 'Ahmad Dahlan Sendiri',
        ]);

        // Verifikasi stok berkurang
        $item1->refresh();
        $item2->refresh();
        $this->assertEquals(24, $item1->stock);
        $this->assertEquals(49, $item2->stock);

        // Verifikasi mutasi keluar tercatat
        $this->assertDatabaseHas('equipment_mutations', [
            'equipment_id' => $item1->id,
            'type' => 'out',
            'quantity' => 1,
            'stock_before' => 25,
            'stock_after' => 24,
        ]);
    }

    /**
     * Test validasi stok tidak mencukupi saat distribusi.
     */
    public function test_cannot_distribute_more_than_available_stock(): void
    {
        $jamaah = Jamaah::create([
            'registration_number' => 'REG-TST-002',
            'name' => 'Siti Aminah Test',
            'nik' => '3201019901010002',
            'gender' => 'P',
            'birth_place' => 'Surabaya',
            'birth_date' => '1992-05-10',
            'phone' => '081234567891',
            'address' => 'Jl. Pahlawan No. 2',
            'status' => 'aktif',
        ]);

        $item = Equipment::create([
            'name' => 'Batik Travel Eksklusif',
            'code' => 'EQ-BTK-01',
            'unit' => 'meter',
            'stock' => 2,
            'min_stock' => 1,
            'is_active' => true,
        ]);

        $distributionData = [
            'jamaah_id' => $jamaah->id,
            'distribution_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'equipment_id' => $item->id,
                    'quantity' => 5, // melebihi stok yang hanya 2
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('equipment.distributions.store'), $distributionData);

        $response->assertSessionHasErrors('items');
        $item->refresh();
        $this->assertEquals(2, $item->stock); // Stok tidak boleh berubah
    }

    /**
     * Test pembatalan distribusi mengembalikan stok secara otomatis.
     */
    public function test_can_cancel_distribution_and_rolls_back_stock(): void
    {
        $jamaah = Jamaah::create([
            'registration_number' => 'REG-TST-003',
            'name' => 'Umar Wirahadi Test',
            'nik' => '3201019901010003',
            'gender' => 'L',
            'birth_place' => 'Medan',
            'birth_date' => '1988-03-15',
            'phone' => '081234567892',
            'address' => 'Jl. Sudirman No. 3',
            'status' => 'aktif',
        ]);

        $item = Equipment::create([
            'name' => 'Buku Doa Saku',
            'code' => 'EQ-BKD-01',
            'unit' => 'buku',
            'stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        // 1. Distribusikan 2 buku doa
        $storeRes = $this->actingAs($this->adminUser)->post(route('equipment.distributions.store'), [
            'jamaah_id' => $jamaah->id,
            'distribution_date' => now()->format('Y-m-d'),
            'received_by' => 'Umar Sendiri',
            'items' => [
                [
                    'equipment_id' => $item->id,
                    'quantity' => 2,
                ],
            ],
        ]);
        $storeRes->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertEquals(8, $item->stock);

        $distribution = EquipmentDistribution::where('jamaah_id', $jamaah->id)->firstOrFail();
        $this->assertEquals('diserahkan', $distribution->status);

        // 2. Batalkan distribusi
        $cancelResponse = $this->actingAs($this->adminUser)->patch(route('equipment.distributions.cancel', $distribution), [
            'cancellation_reason' => 'Jamaah menunda keberangkatan dan mengembalikan buku doa.',
        ]);

        $cancelResponse->assertSessionHasNoErrors();
        $distribution->refresh();
        $item->refresh();

        $this->assertEquals('dibatalkan', $distribution->status);
        $this->assertEquals(10, $item->stock); // Stok kembali jadi 10

        // Verifikasi mutasi adjustment pengembalian stok tercatat
        $this->assertDatabaseHas('equipment_mutations', [
            'equipment_id' => $item->id,
            'type' => 'adjustment',
            'quantity' => 2,
            'stock_before' => 8,
            'stock_after' => 10,
            'reference_type' => 'distribution_cancelled',
        ]);
    }

    /**
     * Test seeder peralatan awal 11 item berjalan dengan sempurna.
     */
    public function test_equipment_seeder_populates_11_initial_items(): void
    {
        $this->seed(EquipmentSeeder::class);

        $this->assertEquals(11, Equipment::count());
        $this->assertDatabaseHas('equipments', ['name' => 'Koper']);
        $this->assertDatabaseHas('equipments', ['name' => 'Tas selempang']);
        $this->assertDatabaseHas('equipments', ['name' => 'Syal']);
        $this->assertDatabaseHas('equipments', ['name' => 'Tali ID Card']);
        $this->assertDatabaseHas('equipments', ['name' => 'Bantal leher']);
        $this->assertDatabaseHas('equipments', ['name' => 'Buku doa']);
        $this->assertDatabaseHas('equipments', ['name' => 'Sampul paspor']);
        $this->assertDatabaseHas('equipments', ['name' => 'Stiker Zamzam']);
        $this->assertDatabaseHas('equipments', ['name' => 'Batik travel']);
        $this->assertDatabaseHas('equipments', ['name' => 'Jilbab']);
        $this->assertDatabaseHas('equipments', ['name' => 'Kain ihram']);
    }

    /**
     * Test CRUD kategori peralatan dan pencegahan penghapusan jika kategori memiliki barang.
     */
    public function test_can_manage_categories_and_cannot_delete_when_has_items(): void
    {
        // 1. Tambah kategori
        $resCreate = $this->actingAs($this->adminUser)->post(route('equipment.categories.store'), [
            'name' => 'Seragam Batik Khusus',
            'description' => 'Kategori seragam dan pakaian',
        ]);
        $resCreate->assertSessionHasNoErrors();
        $category = EquipmentCategory::where('name', 'Seragam Batik Khusus')->firstOrFail();

        // 2. Tambah item pada kategori ini
        Equipment::create([
            'category_id' => $category->id,
            'code' => 'EQ-SRG-01',
            'name' => 'Batik Seragam Pria',
            'unit' => 'pcs',
            'stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        // 3. Coba hapus kategori yang masih memiliki barang (harus ditolak)
        $resDelete = $this->actingAs($this->adminUser)->delete(route('equipment.categories.destroy', $category));
        $resDelete->assertSessionHas('toast_error');
        $this->assertDatabaseHas('equipment_categories', ['id' => $category->id]);

        // 4. Update nama kategori
        $resUpdate = $this->actingAs($this->adminUser)->put(route('equipment.categories.update', $category), [
            'name' => 'Pakaian & Seragam Resmi',
            'description' => 'Deskripsi baru',
        ]);
        $resUpdate->assertSessionHasNoErrors();
        $this->assertDatabaseHas('equipment_categories', [
            'id' => $category->id,
            'name' => 'Pakaian & Seragam Resmi',
        ]);
    }
}
