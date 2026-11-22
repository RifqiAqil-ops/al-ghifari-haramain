<?php

namespace App\Console\Commands\Intelligence;

use App\Services\Intelligence\AlertCenterService;
use App\Services\Intelligence\AutomationEngineService;
use Illuminate\Console\Command;

class RunIntelligenceScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intelligence:scan {--all : Run both alerts and automation rules} {--alerts : Run alert center scan only} {--rules : Run automation rules only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan operational metrics, generate smart alerts, and execute automation rules';

    /**
     * Execute the console command.
     */
    public function handle(
        AlertCenterService $alertService,
        AutomationEngineService $automationService
    ): int {
        $this->info('Starting Al Ghifari Intelligence Scan...');

        $runAlerts = $this->option('all') || $this->option('alerts') || (!$this->option('alerts') && !$this->option('rules'));
        $runRules = $this->option('all') || $this->option('rules') || (!$this->option('alerts') && !$this->option('rules'));

        if ($runAlerts) {
            $this->comment('Scanning operational indicators & smart alerts...');
            $alertResult = $alertService->scanAndGenerateAlerts();
            $this->info("Alert Scan Complete: {$alertResult['new_alerts_count']} new alerts generated. Active alerts: {$alertResult['total_active_alerts']}.");
        }

        if ($runRules) {
            $this->comment('Evaluating scheduled automation rules...');
            $ruleResults = $automationService->runScheduledRules();
            $successCount = collect($ruleResults)->where('status', 'success')->count();
            $skippedCount = collect($ruleResults)->where('status', 'skipped')->count();
            $this->info("Automation Rules Executed: {$successCount} succeeded, {$skippedCount} skipped.");
        }

        $this->info('Al Ghifari Intelligence Scan finished successfully.');
        return Command::SUCCESS;
    }
}
