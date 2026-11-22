<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Al Ghifari Intelligence Periodic Scanner (Runs Hourly)
// Mutex expires in 10 minutes to prevent permanent lockouts if hosting terminates process
Schedule::command('intelligence:scan --all')
    ->hourly()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/intelligence-scan.log'));

// 2. Safe cPanel Queue Worker (Drains database jobs, max 50 seconds to respect PHP max_execution_time)
// Runs every minute via cPanel cron: * * * * * cd /home/xonkgvsz/al-ghifari-core && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->appendOutputTo(storage_path('logs/queue-worker.log'));
