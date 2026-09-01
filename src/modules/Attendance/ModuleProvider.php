<?php

namespace Modules\Attendance;

use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }

    public function register(): void
    {
        $this->app->register(RouterServiceProvider::class);
    }
}
