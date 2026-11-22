<?php

namespace App\Services\Intelligence;

use App\Models\AutomationLog;
use App\Models\AutomationRule;
use App\Models\Notification;
use App\Models\SmartInsight;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Intelligence\AlertCenterService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutomationEngineService
{
    protected AlertCenterService $alertService;

    public function __construct(AlertCenterService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Run all active scheduled automation rules with idempotency protection.
     */
    public function runScheduledRules(): array
    {
        $rules = AutomationRule::where('is_active', true)->get();
        $results = [];

        foreach ($rules as $rule) {
            $results[] = $this->executeRule($rule, 'scheduler');
        }

        return $results;
    }

    /**
     * Execute a specific automation rule.
     */
    public function executeRule(AutomationRule $rule, string $source = 'manual', ?User $executor = null): array
    {
        $todayStr = Carbon::today()->toDateString();
        $idempotencyKey = "rule_{$rule->id}_{$todayStr}";

        // Idempotency check: if already executed today with success, skip
        $alreadyRun = AutomationLog::where('idempotency_key', $idempotencyKey)
            ->where('status', 'success')
            ->exists();

        if ($alreadyRun && $source === 'scheduler') {
            return [
                'rule_id' => $rule->id,
                'name' => $rule->name,
                'status' => 'skipped',
                'message' => 'Rule sudah dieksekusi hari ini (Idempotent Guard).',
            ];
        }

        try {
            $actionResult = [];

            // Dispatch based on rule category
            if ($rule->category === 'billing' || $rule->category === 'equipment' || $rule->category === 'departure' || $rule->category === 'general') {
                $scanResult = $this->alertService->scanAndGenerateAlerts();
                $actionResult = [
                    'scan_result' => $scanResult,
                    'alerts_active' => SmartInsight::where('status', 'active')->count(),
                ];
            }

            // If rule configures system notification
            if (!empty($rule->action_payload['create_notification'])) {
                Notification::create([
                    'user_id' => null, // Broadcast to all staff
                    'type' => 'intelligence_alert',
                    'title' => 'Otomasi Sistem: ' . $rule->name,
                    'message' => 'Aturan otomasi telah memindai data dan memperbarui status peringatan terkini.',
                    'link' => route('intelligence.alerts.index'),
                ]);
            }

            $rule->update(['last_run_at' => now()]);

            $log = AutomationLog::create([
                'rule_id' => $rule->id,
                'trigger_source' => $source,
                'action_taken' => "Eksekusi {$rule->name} ({$rule->action_type})",
                'status' => 'success',
                'idempotency_key' => $idempotencyKey,
                'result_data' => $actionResult,
                'executed_by' => $executor?->id,
            ]);

            return [
                'rule_id' => $rule->id,
                'name' => $rule->name,
                'status' => 'success',
                'log_id' => $log->id,
                'result' => $actionResult,
            ];
        } catch (\Throwable $e) {
            Log::error("Automation Rule {$rule->id} Error: " . $e->getMessage());

            AutomationLog::create([
                'rule_id' => $rule->id,
                'trigger_source' => $source,
                'action_taken' => "Eksekusi {$rule->name}",
                'status' => 'failed',
                'idempotency_key' => $idempotencyKey . '_err_' . time(),
                'error_message' => $e->getMessage(),
                'executed_by' => $executor?->id,
            ]);

            return [
                'rule_id' => $rule->id,
                'name' => $rule->name,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }
}
