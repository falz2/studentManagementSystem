<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('marketplace:process-recurring-orders')->dailyAt('06:00')->withoutOverlapping();
Schedule::command('marketplace:send-inventory-alerts')->dailyAt('06:15')->withoutOverlapping();
Schedule::command('delivery-shares:purge-expired')->hourly()->withoutOverlapping();
