<?php

namespace Modules\Attendance;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouterServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\Attendance\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
    }

    protected function mapApiRoutes(): void
    {
        $version = config('api.version');
        $path = __DIR__ . '/Routes/api/' . $version . '/api.php';

        Route::prefix('api/' . $version)
            ->middleware('api')
            ->group($path);
    }
}
