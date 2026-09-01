<?php

namespace Modules\Animals;

use Modules\ModuleServiceProvider;
use Modules\Animals\Models\Animal;
use Modules\Core\Helpers\SitemapHelper;
use Modules\User\Helpers\PermissionHelper;

class ModuleProvider extends ModuleServiceProvider
{

    public function boot(SitemapHelper $sitemapHelper): void
    {

        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        if(is_installed() and Animal::isEnable()){

            $sitemapHelper->add("animal",[app()->make(Animal::class),'getForSitemap']);
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
            'animal' => Animal::class,
        ];
    }
}
