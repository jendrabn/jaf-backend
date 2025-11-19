<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();

Schedule::command('deactivate-expired-coupons')->everyMinute();
Schedule::command('cancel-expired-order')->everyMinute();
// Schedule::command('orders:track-waybills')->everyFiveMinutes();

Schedule::command('backup:database --compress=1 --keep=14')->dailyAt('02:00')->timezone('Asia/Jakarta')->withoutOverlapping();
