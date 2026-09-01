<?php
namespace Modules\Booking;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouterServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Booking\Controllers';
    protected $moduleApiNamespace = 'Modules\Booking\Api';

    protected $adminModuleNamespace = 'Modules\Booking\Admin';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void
    {
        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes(): void
    {
        $version = config('api.version');
        $path = __DIR__ . '/Routes/api/' . $version . '/api.php';

        Route::prefix('api/' . $version)
            ->middleware('api')
            ->group($path);
    }
}
