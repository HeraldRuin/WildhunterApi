<?php

namespace Modules\Booking;

use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Gateways\PaykeeperGateway;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;


class ModuleProvider extends ModuleServiceProvider
{
    public function boot(SitemapHelper $sitemapHelper)
    {
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(RouterServiceProvider::class);
        $this->app->bind(PaymentGatewayInterface::class, PaykeeperGateway::class);
//        $this->app->register(EventServiceProvider::class);
    }

}
