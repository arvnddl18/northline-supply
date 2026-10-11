<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule heartbeat check every minute
Schedule::command('inspire')->everyMinute();

// S2-T6: Scan due balances and dispatch reminder emails daily at 09:00
Schedule::command('app:send-balance-reminders')->dailyAt('09:00');

// S4-T3: Automated off-session charge runner for due schedules (hourly)
Schedule::command('app:process-due-schedules')->hourly();
