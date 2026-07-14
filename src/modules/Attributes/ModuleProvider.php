<?php

namespace Modules\Attributes;

use Modules\ModuleServiceProvider;
use Modules\Attributes\RouterServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        $this->mergeConfigFrom(__DIR__ . '/Configs/config.php', 'role');

    }
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(RouterServiceProvider::class);
    }
}
