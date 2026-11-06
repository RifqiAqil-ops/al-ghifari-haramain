<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\CompanySetting;
use App\Models\TransactionCategory;
use App\Models\WhatsAppTemplate;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Company Settings
        CompanySetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Al Ghifari Haramain',
                'brand_tagline' => 'UMROH & HAJI SPECIALIST',
                'email' => 'operasional@alghifari.com',
                'phone' => '+62 21 8899 7788',
                'whatsapp' => '6281234567890',
                'address' => 'Gedung Wisma Al Ghifari Lt. 2, Jl. Madinah Raya No. 99, Jakarta Selatan, DKI Jakarta 12560',
                'website' => 'https://alghifariharamain.com',
                'registration_prefix' => 'JAM',
                'invoice_prefix' => 'INV',
                'receipt_prefix' => 'KWT',
                'receipt_footer_note' => 'Kwitansi ini adalah bukti pembayaran sah dari PT. Al Ghifari Haramain. Harap disimpan dengan baik untuk keperluan validasi keberangkatan.',
            ]
        );

        // 2. Bank Accounts
        $bankAccounts = [
            [
                'bank_name' => 'Bank Syariah Indonesia (BSI)',
                'account_number' => '7123456789',
                'account_name' => 'PT AL GHIFARI HARAMAIN TRAVEL',
                'branch' => 'KC Jakarta Rasuna Said',
                'is_active' => true,
            ],
            [
                'bank_name' => 'Bank Mandiri',
                'account_number' => '1270009876543',
                'account_name' => 'PT AL GHIFARI HARAMAIN TRAVEL',
                'branch' => 'KCP Falatehan',
                'is_active' => true,
            ],
            [
                'bank_name' => 'Bank BCA',
                'account_number' => '5420198765',
                'account_name' => 'PT AL GHIFARI HARAMAIN',
                'branch' => 'KCU Melawai',
                'is_active' => true,
            ],
        ];

        foreach ($bankAccounts as $acc) {
            BankAccount::firstOrCreate(['account_number' => $acc['account_number']], $acc);
        }

        // 3. Transaction Categories
        $categories = [
            ['name' => 'Pembayaran Paket Jamaah', 'type' => 'pemasukan', 'description' => 'DP, cicilan, dan pelunasan paket'],
            ['name' => 'Tiket Penerbangan & Maskapai', 'type' => 'pengeluaran', 'description' => 'Biaya tiket pesawat reguler & charter'],
            ['name' => 'Akomodasi & Hotel Saudi', 'type' => 'pengeluaran', 'description' => 'Booking hotel Makkah dan Madinah'],
            ['name' => 'Visa Haji & Umrah', 'type' => 'pengeluaran', 'description' => 'Biaya proses visa e-Haramain / Muassasah'],
            ['name' => 'Transportasi & Bus Busway Saudi', 'type' => 'pengeluaran', 'description' => 'Sewa bus ziarah dan transfer bandara'],
            ['name' => 'Katering & Konsumsi', 'type' => 'pengeluaran', 'description' => 'Konsumsi jamaah fullboard menu Indonesia'],
            ['name' => 'Honor Muthawwif & Tour Leader', 'type' => 'pengeluaran', 'description' => 'Ujrah pembimbing ibadah dan petugas'],
            ['name' => 'Perlengkapan Jamaah', 'type' => 'pengeluaran', 'description' => 'Koper, kain ihram, seragam, mukena'],
            ['name' => 'Operasional Kantor & Administrasi', 'type' => 'pengeluaran', 'description' => 'Listrik, internet, operasional'],
        ];

        foreach ($categories as $cat) {
            TransactionCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // 4. WhatsApp Templates
        $waTemplates = [
            [
                'name' => 'Konfirmasi Pembayaran Diterima',
                'code' => 'konfirmasi_pembayaran',
                'category' => 'pembayaran',
                'body' => "Assalamu'alaikum Wr. Wb. Bpk/Ibu {nama},\n\nAlhamdulillah, pembayaran Anda telah berhasil kami terima dan verifikasi:\n\n• No. Kwitansi: {no_kwitansi}\n• Nominal: {nominal}\n• Jenis: {jenis_pembayaran}\n• Paket: {nama_paket}\n• Sisa Tagihan: {sisa_tagihan}\n\nTerima kasih atas kepercayaannya bersama Al Ghifari Haramain (Umroh & Haji Specialist).\nWassalamu'alaikum Wr. Wb.",
            ],
            [
                'name' => 'Pengingat Pembayaran Jatuh Tempo',
                'code' => 'pengingat_pembayaran',
                'category' => 'pembayaran',
                'body' => "Assalamu'alaikum Wr. Wb. Bpk/Ibu {nama},\n\nSemoga senantiasa dalam limpahan rahmat Allah SWT.\n\nKami mengingatkan kembali terkait tagihan perjalanan ibadah Anda:\n• No. Tagihan: {no_invoice}\n• Paket: {nama_paket}\n• Sisa Tagihan: {sisa_tagihan}\n• Batas Waktu: {jatuh_tempo}\n\nPembayaran dapat ditransfer ke rekening resmi kami: BSI 7123456789 a.n PT AL GHIFARI HARAMAIN TRAVEL.\n\nKonfirmasi bukti transfer dapat dikirimkan ke nomor ini.\nWassalamu'alaikum Wr. Wb.",
            ],
            [
                'name' => 'Pengingat Kelengkapan Dokumen',
                'code' => 'pengingat_dokumen',
                'category' => 'dokumen',
                'body' => "Assalamu'alaikum Wr. Wb. Bpk/Ibu {nama},\n\nMenjelang proses penerbitan visa dan manifes perjalanan paket {nama_paket}, mohon kesediaan Bapak/Ibu untuk melengkapi berkas berikut:\n{daftar_dokumen}\n\nBerkas dapat diantar langsung ke kantor Al Ghifari Haramain atau dikirimkan via kurir paling lambat tanggal {batas_dokumen}.\nWassalamu'alaikum Wr. Wb.",
            ],
            [
                'name' => 'Informasi Jadwal Keberangkatan',
                'code' => 'info_keberangkatan',
                'category' => 'keberangkatan',
                'body' => "Assalamu'alaikum Wr. Wb. Bpk/Ibu {nama},\n\nLabbaik Allahumma Labbaik. Berikut rincian jadwal keberangkatan ibadah Anda:\n\n• Paket: {nama_paket}\n• Tanggal Berangkat: {tanggal_berangkat}\n• Maskapai & No. Flight: {maskapai_flight}\n• Waktu Kumpul: {waktu_kumpul}\n• Titik Kumpul: Terminal 3 Bandara Soekarno-Hatta (CGK)\n• Tour Leader: {tour_leader} ({kontak_tl})\n\nSemoga diberikan kelancaran dan meraih ibadah mabrur. Aamiin.\nWassalamu'alaikum Wr. Wb.",
            ],
        ];

        foreach ($waTemplates as $tmpl) {
            WhatsAppTemplate::firstOrCreate(['code' => $tmpl['code']], $tmpl);
        }
    }
}
