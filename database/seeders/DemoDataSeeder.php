<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\ActivityLog;
use App\Models\Departure;
use App\Models\DepartureChecklist;
use App\Models\Expense;
use App\Models\Flight;
use App\Models\Invoice;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Models\Jamaah;
use App\Models\JamaahContact;
use App\Models\JamaahDocument;
use App\Models\Notification;
use App\Models\Package;
use App\Models\PackageFacility;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::where('email', 'superadmin@alghifari.com')->first();
        $financeUser = User::where('email', 'finance@alghifari.com')->first();

        // 1. Packages
        $packagesData = [
            [
                'code' => 'PKG-UMR-001',
                'name' => 'Umrah Reguler Awwalussanah 9 Hari',
                'type' => 'umrah',
                'program_category' => 'Reguler',
                'price' => 29500000,
                'quad_price' => 29500000,
                'triple_price' => 31500000,
                'double_price' => 34000000,
                'duration_days' => 9,
                'quota' => 45,
                'airline' => 'Saudia Airlines (Direct Madinah)',
                'hotel_makkah' => 'Pullman Zamzam Makkah (Bintang 5)',
                'hotel_makkah_rating' => 5,
                'hotel_madinah' => 'Rove Al Madinah (Bintang 5)',
                'hotel_madinah_rating' => 5,
                'start_period' => Carbon::now()->addDays(15),
                'end_period' => Carbon::now()->addDays(24),
                'description' => 'Paket Umrah 9 hari awal musim dengan penerbangan langsung Saudia Airlines. Fasilitas hotel bintang 5 tepat di pelataran Masjidil Haram & Nabawi.',
                'is_promo' => false,
                'discount_amount' => 0,
                'status' => 'aktif',
                'facilities' => ['Tiket Pesawat PP Saudia Airlines', 'Visa Umrah & Tasreeh', 'Hotel Bintang 5 Fullboard', 'Bus Eksekutif AC 2024', 'Muthawwif Berpengalaman', 'Ziarah Makkah & Madinah', 'Air Zamzam 5 Liter (Sesuai Regulasi)', 'Asuransi Perjalanan Syariah', 'Koper & Perlengkapan Eksklusif Al Ghifari'],
            ],
            [
                'code' => 'PKG-UMR-002',
                'name' => 'Umrah Plus Turki Winter Season 12 Hari',
                'type' => 'umrah',
                'program_category' => 'Plus Turki',
                'price' => 37500000,
                'quad_price' => 37500000,
                'triple_price' => 39500000,
                'double_price' => 42000000,
                'duration_days' => 12,
                'quota' => 40,
                'airline' => 'Turkish Airlines',
                'hotel_makkah' => 'Swissotel Makkah Clock Tower (Bintang 5)',
                'hotel_makkah_rating' => 5,
                'hotel_madinah' => 'Frontel Al Harithia (Bintang 5)',
                'hotel_madinah_rating' => 5,
                'start_period' => Carbon::now()->addDays(35),
                'end_period' => Carbon::now()->addDays(47),
                'description' => 'Perjalanan ibadah Umrah dilanjutkan wisata sejarah Islam di Istanbul dan Bursa Turki saat musim dingin.',
                'is_promo' => true,
                'discount_amount' => 1500000,
                'status' => 'aktif',
                'facilities' => ['Tiket Pesawat PP Turkish Airlines', 'Visa Umrah & E-Visa Turki', 'Hotel Bintang 5 Makkah & Madinah', 'City Tour Istanbul & Bursa', 'Bosphorus Cruise Private Boat', 'Makan 3x Sehari Menu Halal', 'Muthawwif & Tour Guide Lokal Turki'],
            ],
            [
                'code' => 'PKG-HAJ-001',
                'name' => 'Haji Furoda Mujamalah VIP 2026',
                'type' => 'haji',
                'program_category' => 'Haji Furoda / Mujamalah',
                'haji_year' => 2026,
                'price' => 285000000,
                'quad_price' => 285000000,
                'triple_price' => 300000000,
                'double_price' => 325000000,
                'duration_days' => 27,
                'quota' => 25,
                'airline' => 'Garuda Indonesia Direct Jeddah',
                'hotel_makkah' => 'Fairmont Makkah Clock Royal Tower',
                'hotel_makkah_rating' => 5,
                'hotel_madinah' => 'Dar Al Taqwa Madinah',
                'hotel_madinah_rating' => 5,
                'start_period' => Carbon::now()->addMonths(6),
                'end_period' => Carbon::now()->addMonths(6)->addDays(27),
                'description' => 'Program Haji Furoda resmi dengan Visa Mujamalah Kerajaan Arab Saudi tanpa antre. Maktab VIP Tenda Ber-AC Mina & Arafah.',
                'is_promo' => false,
                'discount_amount' => 0,
                'status' => 'aktif',
                'facilities' => ['Visa Haji Mujamalah Resmi Kemenag/Saudi', 'Tenda AC Maktab VIP Arafah & Mina', 'Hotel Bintang 5 Ring 1 Makkah & Madinah', 'Penerbangan Garuda Indonesia Direct', 'Bimbingan Manasik Intensif', 'Dokter Pendamping Pribadi Travel', 'Full Konsumsi Katering Nusantara & Masyaer'],
            ],
        ];

        $createdPackages = [];
        foreach ($packagesData as $pkgData) {
            $facilities = $pkgData['facilities'];
            unset($pkgData['facilities']);

            $pkg = Package::firstOrCreate(['code' => $pkgData['code']], $pkgData);
            $createdPackages[$pkg->code] = $pkg;

            foreach ($facilities as $fac) {
                PackageFacility::firstOrCreate([
                    'package_id' => $pkg->id,
                    'name' => $fac,
                ], [
                    'is_included' => true,
                ]);
            }
        }

        // 2. Departures
        $dep1 = Departure::firstOrCreate(['code' => 'DEP-2026-001'], [
            'package_id' => $createdPackages['PKG-UMR-001']->id,
            'departure_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
            'return_date' => Carbon::now()->addDays(24)->format('Y-m-d'),
            'origin_airport' => 'CGK - Soekarno-Hatta Terminal 3',
            'destination_airport' => 'MED - Prince Mohammad Bin Abdulaziz',
            'airline' => 'Saudia Airlines',
            'flight_number' => 'SV 819',
            'departure_time' => '11:00:00',
            'arrival_time' => '17:30:00',
            'quota' => 45,
            'leader_name' => 'Ustadz Abdullah As-Salafi',
            'leader_phone' => '081299887766',
            'status' => 'persiapan',
            'notes' => 'Manifest rombongan kloter 1 musim 1448H. Kumpul di Lounge Zukafia Terminal 3.',
        ]);

        $dep2 = Departure::firstOrCreate(['code' => 'DEP-2026-002'], [
            'package_id' => $createdPackages['PKG-UMR-002']->id,
            'departure_date' => Carbon::now()->addDays(35)->format('Y-m-d'),
            'return_date' => Carbon::now()->addDays(47)->format('Y-m-d'),
            'origin_airport' => 'CGK - Soekarno-Hatta Terminal 3',
            'destination_airport' => 'IST - Istanbul Airport',
            'airline' => 'Turkish Airlines',
            'flight_number' => 'TK 057',
            'departure_time' => '21:40:00',
            'arrival_time' => '05:30:00',
            'quota' => 40,
            'leader_name' => 'Ustadz H. Rahmat Hidayat, Lc.',
            'leader_phone' => '081377665544',
            'status' => 'persiapan',
            'notes' => 'Pakaian hangat wajib dibawa jamaah untuk ziarah musim dingin di Turki.',
        ]);

        // Flights & Accommodations & Itineraries for Departure 1
        Flight::firstOrCreate([
            'departure_id' => $dep1->id,
            'flight_number' => 'SV 819',
        ], [
            'flight_type' => 'keberangkatan',
            'airline' => 'Saudia Airlines',
            'departure_airport' => 'CGK Jakarta',
            'arrival_airport' => 'MED Madinah',
            'departure_time' => Carbon::now()->addDays(15)->setTime(11, 0),
            'arrival_time' => Carbon::now()->addDays(15)->setTime(17, 30),
            'terminal' => 'Terminal 3 Ultimate',
            'notes' => 'Bawa koper kabin max 7kg',
        ]);

        Flight::firstOrCreate([
            'departure_id' => $dep1->id,
            'flight_number' => 'SV 818',
        ], [
            'flight_type' => 'kepulangan',
            'airline' => 'Saudia Airlines',
            'departure_airport' => 'JED Jeddah',
            'arrival_airport' => 'CGK Jakarta',
            'departure_time' => Carbon::now()->addDays(24)->setTime(19, 0),
            'arrival_time' => Carbon::now()->addDays(25)->setTime(9, 0),
            'terminal' => 'Terminal 1 Hajj Hall Jeddah',
            'notes' => 'Penerbangan kepulangan menuju tanah air',
        ]);

        Accommodation::firstOrCreate([
            'departure_id' => $dep1->id,
            'city' => 'Madinah',
            'hotel_name' => 'Rove Al Madinah',
        ], [
            'check_in_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(19)->format('Y-m-d'),
            'address' => 'Northern Central Area, Badaah, Madinah 42311',
            'contact_phone' => '+966 14 829 8888',
        ]);

        Accommodation::firstOrCreate([
            'departure_id' => $dep1->id,
            'city' => 'Makkah',
            'hotel_name' => 'Pullman Zamzam Makkah',
        ], [
            'check_in_date' => Carbon::now()->addDays(19)->format('Y-m-d'),
            'check_out_date' => Carbon::now()->addDays(24)->format('Y-m-d'),
            'address' => 'Abraj Al Bait Complex, King Abdul Aziz Endowment, Makkah 21955',
            'contact_phone' => '+966 12 571 5555',
        ]);

        // Itinerary
        $itin = Itinerary::firstOrCreate(['departure_id' => $dep1->id], [
            'title' => 'Itinerary 9 Hari Umrah Reguler Awwalussanah',
            'description' => 'Jadwal rangkaian ibadah dan ziarah Madinah Al-Munawwarah & Makkah Al-Mukarramah',
        ]);

        $itinItems = [
            ['day_number' => 1, 'title' => 'Keberangkatan Jakarta ke Madinah', 'activity' => 'Kumpul di Bandara Soetta Terminal 3, proses check-in, penerbangan SV819 menuju Madinah. Tiba di Madinah langsung check-in hotel dan istirahat.', 'location' => 'Jakarta - Madinah'],
            ['day_number' => 2, 'title' => 'Ziarah Raudhah & Masjid Nabawi', 'activity' => 'Melaksanakan sholat fardhu berjamaah di Masjid Nabawi, ziarah Makam Rasulullah SAW, Abu Bakar RA, Umar RA, dan ziarah Raudhah Asy-Syarifah dengan Tasreeh resmi.', 'location' => 'Masjid Nabawi, Madinah'],
            ['day_number' => 3, 'title' => 'Ziarah Kota Madinah', 'activity' => 'Ziarah Masjid Quba, Kebun Kurma, Jabal Uhud & Makam Syuhada Uhud, Masjid Qiblatain, dan Khandaq.', 'location' => 'Kota Madinah'],
            ['day_number' => 4, 'title' => 'Menuju Makkah & Umrah Pertama', 'activity' => 'Mandi ihram di hotel Madinah, berangkat menuju Masjid Bir Ali untuk Miqat dan berniat Umrah. Perjalanan ke Makkah via Kereta Cepat Haramain. Tiba di Makkah check-in hotel dan melaksanakan thawaf, sa\'i, tahallul.', 'location' => 'Madinah - Bir Ali - Makkah'],
            ['day_number' => 5, 'title' => 'Ibadah Mandiri di Masjidil Haram', 'activity' => 'Memperbanyak thawaf sunnah, tilawah Al-Quran, dan sholat fardhu berjamaah di Masjidil Haram.', 'location' => 'Masjidil Haram, Makkah'],
            ['day_number' => 6, 'title' => 'Ziarah Kota Makkah & Umrah Kedua', 'activity' => 'Ziarah Jabal Tsur, Padang Arafah, Jabal Rahmah, Muzdalifah, Mina, dan Miqat di Ji\'ranah bagi jamaah yang ingin Umrah kedua.', 'location' => 'Kota Makkah'],
            ['day_number' => 7, 'title' => 'Ziarah Hudaibiyah / Museum Haramain', 'activity' => 'Kunjungan ke Peternakan Unta Hudaibiyah dan Museum Dua Kota Suci.', 'location' => 'Hudaibiyah, Makkah'],
            ['day_number' => 8, 'title' => 'Thawaf Wada\' & Menuju Jeddah', 'activity' => 'Melaksanakan Thawaf Wada\' (perpisahan). Check-out hotel Makkah menuju Bandara King Abdulaziz Jeddah via Corniche City Tour.', 'location' => 'Makkah - Jeddah'],
            ['day_number' => 9, 'title' => 'Tiba di Jakarta', 'activity' => 'Penerbangan SV818 tiba di Bandara Internasional Soekarno-Hatta Jakarta. Pembagian air zamzam dan penutupan program.', 'location' => 'CGK Jakarta'],
        ];

        foreach ($itinItems as $item) {
            ItineraryItem::firstOrCreate([
                'itinerary_id' => $itin->id,
                'day_number' => $item['day_number'],
            ], $item);
        }

        // Checklists
        $checklists = [
            ['item_name' => 'Verifikasi Paspor Asli Jamaah (Min. Masa Berlaku 7 Bulan)', 'category' => 'dokumen', 'is_completed' => true],
            ['item_name' => 'Penerbitan Visa Umrah Elektronik & Tasreeh Raudhah', 'category' => 'visa', 'is_completed' => true],
            ['item_name' => 'Konfirmasi Tiket Saudia Airlines Group PNR', 'category' => 'tiket', 'is_completed' => true],
            ['item_name' => 'Voucher Hotel Rove Madinah & Pullman Zamzam Makkah', 'category' => 'hotel', 'is_completed' => true],
            ['item_name' => 'Distribusi Koper, Ihram & Seragam Batik Al Ghifari', 'category' => 'perlengkapan', 'is_completed' => true],
            ['item_name' => 'Briefing Manasik Umrah Akbar Pra-Keberangkatan', 'category' => 'logistik', 'is_completed' => false],
            ['item_name' => 'Penjemputan Bus SAPTCO VIP Jeddah-Madinah-Makkah', 'category' => 'logistik', 'is_completed' => false],
        ];

        foreach ($checklists as $chk) {
            DepartureChecklist::firstOrCreate([
                'departure_id' => $dep1->id,
                'item_name' => $chk['item_name'],
            ], $chk);
        }

        // 3. Sample Jamaahs & Registrations
        $jamaahsData = [
            [
                'registration_number' => 'JAM-202610-0001',
                'name' => 'H. Muhammad Arifin, S.E.',
                'nik' => '3171011505750001',
                'family_card_number' => '3171011505750000',
                'passport_number' => 'X1234567',
                'passport_expiry_date' => Carbon::now()->addYears(5),
                'passport_issued_place' => 'Jakarta Selatan',
                'birth_place' => 'Jakarta',
                'birth_date' => '1975-05-15',
                'gender' => 'L',
                'address' => 'Jl. Tebet Barat Raya No. 45, RT 05 / RW 02',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12810',
                'phone' => '081288990011',
                'email' => 'arifin.jakarta@gmail.com',
                'emergency_contact_name' => 'Hj. Ratna Sari (Istri)',
                'emergency_contact_relation' => 'istri',
                'emergency_contact_phone' => '081288990022',
                'special_needs' => null,
                'status' => 'aktif',
                'room_type' => 'double',
                'agreed_price' => 34000000,
                'paid_amount' => 34000000, // Lunas
                'payment_type' => 'pelunasan',
            ],
            [
                'registration_number' => 'JAM-202610-0002',
                'name' => 'Hj. Ratna Sari, M.Pd.',
                'nik' => '3171015208780002',
                'family_card_number' => '3171011505750000',
                'passport_number' => 'X1234568',
                'passport_expiry_date' => Carbon::now()->addYears(5),
                'passport_issued_place' => 'Jakarta Selatan',
                'birth_place' => 'Bandung',
                'birth_date' => '1978-08-12',
                'gender' => 'P',
                'address' => 'Jl. Tebet Barat Raya No. 45, RT 05 / RW 02',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12810',
                'phone' => '081288990022',
                'email' => 'ratna.sari@gmail.com',
                'emergency_contact_name' => 'H. Muhammad Arifin (Suami)',
                'emergency_contact_relation' => 'suami',
                'emergency_contact_phone' => '081288990011',
                'special_needs' => null,
                'status' => 'aktif',
                'room_type' => 'double',
                'agreed_price' => 34000000,
                'paid_amount' => 34000000, // Lunas
                'payment_type' => 'pelunasan',
            ],
            [
                'registration_number' => 'JAM-202610-0003',
                'name' => 'Drs. H. Bambang Soeprapto',
                'nik' => '3275021004600003',
                'family_card_number' => '3275021004600000',
                'passport_number' => 'X2345678',
                'passport_expiry_date' => Carbon::now()->addYears(3),
                'passport_issued_place' => 'Bekasi',
                'birth_place' => 'Yogyakarta',
                'birth_date' => '1960-04-10',
                'gender' => 'L',
                'address' => 'Komplek Kemang Pratama 2 Blok AL No. 12',
                'city' => 'Bekasi',
                'province' => 'Jawa Barat',
                'postal_code' => '17116',
                'phone' => '081311223344',
                'email' => 'bambang.soeprapto@yahoo.com',
                'emergency_contact_name' => 'Dimas Prasetyo (Anak)',
                'emergency_contact_relation' => 'anak',
                'emergency_contact_phone' => '081399887711',
                'special_needs' => 'Memerlukan bantuan kursi roda saat Thawaf & Sa\'i karena usia lanjut.',
                'status' => 'aktif',
                'room_type' => 'quad',
                'agreed_price' => 29500000,
                'paid_amount' => 15000000, // Cicilan (Belum Lunas)
                'payment_type' => 'cicilan',
            ],
            [
                'registration_number' => 'JAM-202610-0004',
                'name' => 'Fathurrahman Al-Ayubi',
                'nik' => '3204122501950004',
                'family_card_number' => '3204122501950000',
                'passport_number' => 'X3456789',
                'passport_expiry_date' => Carbon::now()->addYears(7),
                'passport_issued_place' => 'Bandung',
                'birth_place' => 'Bandung',
                'birth_date' => '1995-01-25',
                'gender' => 'L',
                'address' => 'Jl. Dago Asri No. 8',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'postal_code' => '40135',
                'phone' => '085722334455',
                'email' => 'fathur.ayubi@gmail.com',
                'emergency_contact_name' => 'Ayahanda Ridwan (Orang Tua)',
                'emergency_contact_relation' => 'orang_tua',
                'emergency_contact_phone' => '085711223399',
                'special_needs' => null,
                'status' => 'aktif',
                'room_type' => 'quad',
                'agreed_price' => 29500000,
                'paid_amount' => 5000000, // DP (Belum Lunas)
                'payment_type' => 'dp',
            ],
            [
                'registration_number' => 'JAM-202610-0005',
                'name' => 'Aisyah Putri Wardhani',
                'nik' => '3174056012980005',
                'family_card_number' => '3174056012980000',
                'passport_number' => 'X4567890',
                'passport_expiry_date' => Carbon::now()->addYears(6),
                'passport_issued_place' => 'Jakarta Barat',
                'birth_place' => 'Surabaya',
                'birth_date' => '1998-12-20',
                'gender' => 'P',
                'address' => 'Jl. Puri Kencana Blok K No. 3',
                'city' => 'Jakarta Barat',
                'province' => 'DKI Jakarta',
                'postal_code' => '11610',
                'phone' => '081809988776',
                'email' => 'aisyah.wardhani@gmail.com',
                'emergency_contact_name' => 'H. Wardhani (Ayah)',
                'emergency_contact_relation' => 'orang_tua',
                'emergency_contact_phone' => '081801122334',
                'special_needs' => null,
                'status' => 'aktif',
                'room_type' => 'triple',
                'agreed_price' => 31500000,
                'paid_amount' => 0, // Belum Bayar
                'payment_type' => null,
            ],
            [
                'registration_number' => 'JAM-202610-0006',
                'name' => 'Dr. H. Hendra Gunawan, Sp.PD',
                'nik' => '3674031809700006',
                'family_card_number' => '3674031809700000',
                'passport_number' => 'X5678901',
                'passport_expiry_date' => Carbon::now()->addYears(8),
                'passport_issued_place' => 'Tangerang',
                'birth_place' => 'Medan',
                'birth_date' => '1970-09-18',
                'gender' => 'L',
                'address' => 'Cluster Foresta BSD City Blok C No. 9',
                'city' => 'Tangerang Selatan',
                'province' => 'Banten',
                'postal_code' => '15339',
                'phone' => '081199881122',
                'email' => 'hendra.gunawan.md@gmail.com',
                'emergency_contact_name' => 'Hj. Linda Marlina (Istri)',
                'emergency_contact_relation' => 'istri',
                'emergency_contact_phone' => '081199881133',
                'special_needs' => null,
                'status' => 'aktif',
                'room_type' => 'double',
                'agreed_price' => 36000000,
                'paid_amount' => 36000000, // Lunas
                'payment_type' => 'pelunasan',
            ],
        ];

        $regIndex = 1;
        foreach ($jamaahsData as $jData) {
            $paidAmt = $jData['paid_amount'];
            $pmtType = $jData['payment_type'];
            $roomType = $jData['room_type'];
            $agreedPrice = $jData['agreed_price'];

            unset($jData['paid_amount'], $jData['payment_type'], $jData['room_type'], $jData['agreed_price']);

            $jamaah = Jamaah::firstOrCreate(['registration_number' => $jData['registration_number']], $jData);

            // Contact
            JamaahContact::firstOrCreate([
                'jamaah_id' => $jamaah->id,
                'name' => $jamaah->emergency_contact_name ?? 'Keluarga Jamaah',
            ], [
                'relationship' => $jamaah->emergency_contact_relation ?? 'kerabat',
                'phone' => $jamaah->emergency_contact_phone ?? '08123456789',
                'is_emergency_contact' => true,
            ]);

            // Document samples
            JamaahDocument::firstOrCreate([
                'jamaah_id' => $jamaah->id,
                'document_type' => 'ktp',
            ], [
                'document_name' => 'KTP-' . $jamaah->nik . '.pdf',
                'file_path' => 'documents/ktp_sample.pdf',
                'file_size' => 245000,
                'file_type' => 'application/pdf',
                'status' => 'diverifikasi',
                'verified_at' => now(),
                'verified_by' => $superAdmin->id ?? null,
            ]);

            JamaahDocument::firstOrCreate([
                'jamaah_id' => $jamaah->id,
                'document_type' => 'paspor',
            ], [
                'document_name' => 'Paspor-' . $jamaah->passport_number . '.jpg',
                'file_path' => 'documents/paspor_sample.jpg',
                'file_size' => 450000,
                'file_type' => 'image/jpeg',
                'status' => 'diverifikasi',
                'verified_at' => now(),
                'verified_by' => $superAdmin->id ?? null,
            ]);

            // Registration
            $regNumber = sprintf('REG-202610-%04d', $regIndex);
            $reg = Registration::firstOrCreate(['registration_number' => $regNumber], [
                'jamaah_id' => $jamaah->id,
                'package_id' => $createdPackages['PKG-UMR-001']->id,
                'departure_id' => $dep1->id,
                'registration_date' => Carbon::now()->subDays(rand(5, 20)),
                'room_type' => $roomType,
                'agreed_price' => $agreedPrice,
                'discount' => 0,
                'final_price' => $agreedPrice,
                'status' => $paidAmt >= $agreedPrice ? 'siap_berangkat' : 'proses',
            ]);

            // Invoice
            $invNumber = sprintf('INV-202610-%04d', $regIndex);
            $invoice = Invoice::firstOrCreate(['invoice_number' => $invNumber], [
                'registration_id' => $reg->id,
                'total_amount' => $agreedPrice,
                'paid_amount' => $paidAmt,
                'remaining_amount' => max(0, $agreedPrice - $paidAmt),
                'status' => $paidAmt >= $agreedPrice ? 'lunas' : ($paidAmt > 0 ? 'sebagian' : 'belum_bayar'),
                'due_date' => Carbon::now()->addDays(7),
            ]);

            // Payment record if paid
            if ($paidAmt > 0) {
                $trxNumber = sprintf('TRX-202610-%04d', $regIndex);
                $kwtNumber = sprintf('KWT-202610-%04d', $regIndex);

                Payment::firstOrCreate(['transaction_number' => $trxNumber], [
                    'invoice_id' => $invoice->id,
                    'receipt_number' => $kwtNumber,
                    'payment_date' => Carbon::now()->subDays(rand(1, 10)),
                    'amount' => $paidAmt,
                    'payment_type' => $pmtType,
                    'payment_method' => 'transfer',
                    'bank_name' => 'Bank Syariah Indonesia (BSI)',
                    'sender_name' => $jamaah->name,
                    'status' => 'valid',
                    'received_by' => $financeUser->id ?? null,
                    'notes' => 'Pembayaran ' . $pmtType . ' paket umrah atas nama ' . $jamaah->name,
                ]);
            }

            $regIndex++;
        }

        // 4. Sample Expenses
        $catTiket = TransactionCategory::where('name', 'like', '%Tiket%')->first();
        $catHotel = TransactionCategory::where('name', 'like', '%Hotel%')->first();
        $catVisa = TransactionCategory::where('name', 'like', '%Visa%')->first();

        $expenses = [
            [
                'expense_number' => 'EXP-202610-0001',
                'category_id' => $catTiket->id ?? null,
                'departure_id' => $dep1->id,
                'title' => 'Deposit Booking Tiket Saudia Airlines Group 45 Pax',
                'expense_date' => Carbon::now()->subDays(15),
                'amount' => 125000000,
                'payment_method' => 'transfer',
                'notes' => 'Pembayaran DP tiket penerbangan SV819/818 Jakarta-Madinah PP',
                'recorded_by' => $financeUser->id ?? null,
            ],
            [
                'expense_number' => 'EXP-202610-0002',
                'category_id' => $catHotel->id ?? null,
                'departure_id' => $dep1->id,
                'title' => 'Down Payment Hotel Pullman Zamzam Makkah',
                'expense_date' => Carbon::now()->subDays(10),
                'amount' => 85000000,
                'payment_method' => 'transfer',
                'notes' => 'Booking 12 kamar Quad & Double periode 19-24 Nov',
                'recorded_by' => $financeUser->id ?? null,
            ],
            [
                'expense_number' => 'EXP-202610-0003',
                'category_id' => $catVisa->id ?? null,
                'departure_id' => $dep1->id,
                'title' => 'Biaya Pengurusan Visa Umrah & Asuransi Saudi Kloter 1',
                'expense_date' => Carbon::now()->subDays(4),
                'amount' => 18500000,
                'payment_method' => 'transfer',
                'notes' => 'Proses visa Muassasah Nusuk via portal resmi',
                'recorded_by' => $financeUser->id ?? null,
            ],
        ];

        foreach ($expenses as $exp) {
            Expense::firstOrCreate(['expense_number' => $exp['expense_number']], $exp);
        }

        // 5. Sample Notifications
        Notification::firstOrCreate(['title' => 'Dokumen Jamaah Baru Diverifikasi'], [
            'type' => 'dokumen_lengkap',
            'message' => 'Berkas KTP & Paspor atas nama H. Muhammad Arifin telah berhasil diverifikasi.',
            'link' => '/jamaah/1',
            'is_read' => false,
        ]);

        Notification::firstOrCreate(['title' => 'Pengingat Keberangkatan Mendekat'], [
            'type' => 'keberangkatan_dekat',
            'message' => 'Jadwal keberangkatan DEP-2026-001 tinggal 15 hari lagi. Pastikan seluruh checklist terpenuhi.',
            'link' => '/departures/1',
            'is_read' => false,
        ]);

        Notification::firstOrCreate(['title' => 'Tagihan Pembayaran Jatuh Tempo'], [
            'type' => 'pembayaran_jatuh_tempo',
            'message' => 'Jamaah Drs. H. Bambang Soeprapto memiliki sisa tagihan Rp 14.500.000 jatuh tempo dalam 7 hari.',
            'link' => '/finance/invoices',
            'is_read' => false,
        ]);

        // 6. Sample Activity Logs
        ActivityLog::create([
            'user_id' => $superAdmin->id ?? null,
            'user_name' => 'Super Administrator Al Ghifari',
            'action' => 'INITIALIZE',
            'module' => 'System',
            'description' => 'Inisialisasi sistem database, peran pengguna, dan konfigurasi profil perusahaan Al Ghifari Haramain.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
    }
}
