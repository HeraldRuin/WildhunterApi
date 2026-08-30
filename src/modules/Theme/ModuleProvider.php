<?php

namespace Modules\Theme;

use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        $this->app->register(RouterServiceProvider::class);
    }
}
