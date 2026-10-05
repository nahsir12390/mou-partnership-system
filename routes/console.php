<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('agreements:sync-lifecycle')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('agreements:send-reminders')->dailyAt('08:00')->withoutOverlapping();
