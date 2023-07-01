<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Webmozart\Assert\Assert;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('headerVersion')
                ->group($this->loadHeaderVersionedRoutes());

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    private function loadHeaderVersionedRoutes()
    {
        Route::group([], base_path('routes/api.php'));

        $apiVersion = self::getApiVersion();
        $routeFile = $apiVersion ? base_path(sprintf('routes/api.%s.php', $apiVersion)) : null;

        if ($routeFile && file_exists($routeFile)) {
            Route::group([], $routeFile);
        }
    }

    private static function getApiVersion(): ?string
    {
        // In the test environment, the route service provider is loaded _before_ the request is made,
        // so we can't rely on the header.
        // Instead, we manually set the API version as an env variable in applicable test cases.
        $version = app()->runningUnitTests() ? env('X_API_VERSION') : request()->header('X-Api-Version');

        if ($version) {
            Assert::oneOf($version, ['v2']);
        }

        return $version;
    }
}
