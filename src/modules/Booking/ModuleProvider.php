<?php

namespace Modules\Booking;

use Modules\Booking\Contracts\PaymentGatewayInterface;
use Modules\Booking\Gateways\PaykeeperGateway;
use Modules\Booking\Models\BookingService;
use Modules\Booking\Observers\BookingServiceObserver;
use Modules\Core\Helpers\SitemapHelper;
use Modules\ModuleServiceProvider;

class ModuleProvider extends ModuleServiceProvider
{
    public function boot(SitemapHelper $sitemapHelper)
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        BookingService::observe(BookingServiceObserver::class);
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

    }

}
