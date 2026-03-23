<?php

use App\Console\Commands\ExpireAppointments;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cancel appointments whose date has passed — runs every day at 00:05
Schedule::command(ExpireAppointments::class)->dailyAt('00:05');
