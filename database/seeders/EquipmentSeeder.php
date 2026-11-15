<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentMutation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creator = User::where('email', 'superadmin@alghifari.com')->first();
        $creatorId = $creator?->id;

        $categories = [
            'Koper & Tas' => 'Koper bagasi, tas kabin, dan tas selempang perjalanan',
            'Aksesoris & Identitas' => 'Kelengkapan atribut pengenal dan kenyamanan jamaah',
            'Buku & Dokumen' => 'Buku panduan doa, manasik, dan pelindung berkas jamaah',
            'Pakaian & Ibadah' => 'Kain ihram, batik travel resmi, dan seragam ibadah',
        ];

        $categoryModels = [];
        foreach ($categories as $name => $desc) {
            $categoryModels[$name] = EquipmentCategory::firstOrCreate(
                ['name' => $name],
                ['description' => $desc]
            );
        }

        // 11 Jenis Perlengkapan Awal
        $items = [
            [
                'category' => 'Koper & Tas',
                'code' => 'EQP-KPR',
                'name' => 'Koper',
                'unit' => 'pcs',
                'stock' => 80,
                'min_stock' => 15,
                'description' => 'Koper bagasi resmi Al Ghifari Haramain 24 inch roda 4',
            ],
            [
                'category' => 'Koper & Tas',
                'code' => 'EQP-SLP',
                'name' => 'Tas selempang',
                'unit' => 'pcs',
                'stock' => 100,
                'min_stock' => 20,
                'description' => 'Tas selempang paspor & dokumen perjalanan',
            ],
            [
                'category' => 'Aksesoris & Identitas',
                'code' => 'EQP-SYL',
                'name' => 'Syal',
                'unit' => 'pcs',
                'stock' => 120,
                'min_stock' => 25,
                'description' => 'Syal rajut identitas jamaah Al Ghifari',
            ],
            [
                'category' => 'Aksesoris & Identitas',
                'code' => 'EQP-IDC',
                'name' => 'Tali ID Card',
                'unit' => 'pcs',
                'stock' => 150,
                'min_stock' => 30,
                'description' => 'Lanyard dan pouch kartu identitas jamaah',
            ],
            [
                'category' => 'Aksesoris & Identitas',
                'code' => 'EQP-BNT',
                'name' => 'Bantal leher',
                'unit' => 'pcs',
                'stock' => 75,
                'min_stock' => 15,
                'description' => 'Bantal leher travel memory foam untuk kenyamanan penerbangan',
            ],
            [
                'category' => 'Buku & Dokumen',
                'code' => 'EQP-DOA',
                'name' => 'Buku doa',
                'unit' => 'buku',
                'stock' => 120,
                'min_stock' => 25,
                'description' => 'Buku saku panduan manasik dan kumpulan doa umroh/haji',
            ],
            [
                'category' => 'Buku & Dokumen',
                'code' => 'EQP-PSP',
                'name' => 'Sampul paspor',
                'unit' => 'pcs',
                'stock' => 120,
                'min_stock' => 20,
                'description' => 'Cover pelindung paspor bahan kulit sintetis eksklusif',
            ],
            [
                'category' => 'Aksesoris & Identitas',
                'code' => 'EQP-ZMZ',
                'name' => 'Stiker Zamzam',
                'unit' => 'lembar',
                'stock' => 200,
                'min_stock' => 40,
                'description' => 'Stiker label identitas bagasi air Zamzam',
            ],
            [
                'category' => 'Pakaian & Ibadah',
                'code' => 'EQP-BTK',
                'name' => 'Batik travel',
                'unit' => 'potong',
                'stock' => 90,
                'min_stock' => 20,
                'description' => 'Kain / seragam batik resmi Al Ghifari Haramain',
            ],
            [
                'category' => 'Pakaian & Ibadah',
                'code' => 'EQP-JLB',
                'name' => 'Jilbab',
                'unit' => 'pcs',
                'stock' => 70,
                'min_stock' => 15,
                'description' => 'Jilbab seragam syar\'i untuk jamaah wanita',
            ],
            [
                'category' => 'Pakaian & Ibadah',
                'code' => 'EQP-IHR',
                'name' => 'Kain ihram',
                'unit' => 'set',
                'stock' => 70,
                'min_stock' => 15,
                'description' => 'Satu set kain ihram pria (2 lembar) katun tebal',
            ],
        ];

        foreach ($items as $item) {
            $cat = $categoryModels[$item['category']];
            $existing = Equipment::where('code', $item['code'])->first();

            if (! $existing) {
                $equipment = Equipment::create([
                    'category_id' => $cat->id,
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'unit' => $item['unit'],
                    'stock' => $item['stock'],
                    'min_stock' => $item['min_stock'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]);

                // Catat mutasi awal
                EquipmentMutation::create([
                    'equipment_id' => $equipment->id,
                    'type' => 'in',
                    'quantity' => $item['stock'],
                    'stock_before' => 0,
                    'stock_after' => $item['stock'],
                    'reference_type' => 'restock',
                    'notes' => 'Pencatatan saldo awal stok inventaris',
                    'created_by' => $creatorId,
                ]);
            }
        }
    }
}
