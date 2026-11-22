<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use Illuminate\Database\Seeder;

class IntelligenceRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name' => 'Peringatan Tagihan Jatuh Tempo (H-7)',
                'code' => 'rule_payment_due_h7',
                'category' => 'billing',
                'trigger_type' => 'schedule_daily',
                'conditions' => [
                    'days_before_due' => 7,
                    'minimum_unpaid' => 500000,
                ],
                'action_type' => 'create_alert',
                'action_payload' => [
                    'level' => 'warning',
                    'title' => 'Tagihan Segera Jatuh Tempo',
                    'draft_whatsapp' => true,
                ],
                'is_active' => true,
                'is_autonomous' => false,
            ],
            [
                'name' => 'Peringatan Paspor Kadaluarsa / Belum Lengkap (H-30)',
                'code' => 'rule_passport_incomplete_h30',
                'category' => 'document',
                'trigger_type' => 'schedule_daily',
                'conditions' => [
                    'days_before_departure' => 30,
                    'document_type' => 'paspor',
                ],
                'action_type' => 'create_alert',
                'action_payload' => [
                    'level' => 'critical',
                    'title' => 'Dokumen Paspor Belum Diverifikasi Menjelang Keberangkatan',
                ],
                'is_active' => true,
                'is_autonomous' => false,
            ],
            [
                'name' => 'Deteksi Kesiapan Kloter Rendah (H-14)',
                'code' => 'rule_low_departure_readiness_h14',
                'category' => 'departure',
                'trigger_type' => 'schedule_daily',
                'conditions' => [
                    'days_before_departure' => 14,
                    'min_readiness_percent' => 75,
                ],
                'action_type' => 'create_alert',
                'action_payload' => [
                    'level' => 'critical',
                    'title' => 'Skor Kesiapan Kloter Di Bawah Standar 75%',
                ],
                'is_active' => true,
                'is_autonomous' => false,
            ],
            [
                'name' => 'Peringatan Stok Logistik Di Bawah Ambang Batas',
                'code' => 'rule_low_equipment_stock',
                'category' => 'equipment',
                'trigger_type' => 'schedule_daily',
                'conditions' => [
                    'stock_threshold' => 'min_stock',
                ],
                'action_type' => 'create_alert',
                'action_payload' => [
                    'level' => 'warning',
                    'title' => 'Stok Perlengkapan Menipis',
                ],
                'is_active' => true,
                'is_autonomous' => true,
            ],
            [
                'name' => 'Deteksi Transaksi Pengeluaran Di Luar Batas Wajar (Anomali)',
                'code' => 'rule_anomaly_expense_detection',
                'category' => 'general',
                'trigger_type' => 'schedule_daily',
                'conditions' => [
                    'variance_multiplier' => 2.5,
                ],
                'action_type' => 'create_alert',
                'action_payload' => [
                    'level' => 'warning',
                    'title' => 'Pengeluaran Operasional Tidak Biasa Terdeteksi',
                ],
                'is_active' => true,
                'is_autonomous' => true,
            ],
        ];

        foreach ($rules as $ruleData) {
            AutomationRule::firstOrCreate(
                ['code' => $ruleData['code']],
                $ruleData
            );
        }
    }
}
