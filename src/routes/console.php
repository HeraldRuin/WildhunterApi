<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('payments:process')
    ->everyMinute()
    ->withoutOverlapping()
    ->timezone('Europe/Moscow');

Schedule::command('beds:process-expired')->everyMinute()
    ->withoutOverlapping()
    ->timezone('Europe/Moscow');


