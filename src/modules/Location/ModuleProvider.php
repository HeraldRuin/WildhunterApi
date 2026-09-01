<?php

namespace Modules\Location;

use Modules\ModuleServiceProvider;
use Modules\Location\Models\Location;
use Modules\Core\Helpers\SitemapHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(SitemapHelper $sitemapHelper): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->mergeConfigFrom(__DIR__ . '/Configs/config.php', 'location');

        if (is_installed() and Location::isEnable()) {
            $sitemapHelper->add("location", [app()->make(Location::class), 'getForSitemap']);
        }
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

    public static function getAdminMenu()
    {

    }
}
