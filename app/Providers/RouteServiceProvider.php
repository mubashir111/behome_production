<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // General API: 200 req/min for authenticated users, 60 for guests
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(200)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Auth endpoints (login, register, password reset): 10 req/min per IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Contact form: 5 submissions per 10 minutes per IP
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function mapWebRoutes()
    {
        if (file_exists(storage_path('installed'))) {

            try {
                $files = scandir(__DIR__ . '/../Http/PaymentGateways/Routes');
                if (count($files) > 2) {
                    foreach ($files as $file) {
                        if ($file != '.' && $file != '..') {
                            Route::middleware('web')
                                ->group(__DIR__ . "/../Http/PaymentGateways/Routes/{$file}");
                        }
                    }
                }
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }
        }
    }
}
