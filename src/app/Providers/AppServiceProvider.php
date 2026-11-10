<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Временный тест: APP_TEST_TODAY=Y-m-d подменяет «сегодня» во всём API. Убрать вместе с переменной.
        $testToday = env('APP_TEST_TODAY');
        if (is_string($testToday) && $testToday !== '') {
            Carbon::setTestNow(Carbon::parse($testToday));
        }
    }
}
