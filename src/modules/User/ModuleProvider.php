<?php

namespace Modules\User;

use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(): void
    {

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->mergeConfigFrom(__DIR__ . '/Configs/config.php', 'user');

    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(RouterServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }
}
