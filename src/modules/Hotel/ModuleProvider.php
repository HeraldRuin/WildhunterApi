<?php

namespace Modules\Hotel;

use Modules\Hotel\Models\Hotel;
use Modules\ModuleServiceProvider;
use Modules\Core\Helpers\SitemapHelper;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot(SitemapHelper $sitemapHelper): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if(is_installed() and Hotel::isEnable()){

            $sitemapHelper->add("hotel",[app()->make(Hotel::class),'getForSitemap']);
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

    public static function getBookableServices(): array
    {
        return [
            'hotel' => Hotel::class,
        ];
    }
}
