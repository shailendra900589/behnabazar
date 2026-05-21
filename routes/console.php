<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('cart:abandoned-remind')->hourly();
Schedule::command('stock:notify')->everyThirtyMinutes();
Schedule::command('whatsapp:process-outbox')->everyMinute();
