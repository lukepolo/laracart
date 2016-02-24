<?php

namespace LukePOLO\LaraCart;

use Illuminate\Support\ServiceProvider;

/**
 * Class LaraCartServiceProvider
 *
 * @package LukePOLO\LaraCart
 */
class LaraCartServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/config/laracart.php' => config_path('laracart.php'),
            ]
        );

        $this->mergeConfigFrom(
            __DIR__ . '/config/laracart.php',
            'laracart'
        );

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'migrations'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LaraCart::SERVICE, function ($app) {
            return new LaraCart($app['session'], $app['events'], $app['auth']);
        }
        );

        $this->app->bind(
            LaraCart::HASH,
            function ($app, $data) {
                return md5(json_encode($data));
            }
        );

        $this->app->bind(
            LaraCart::RANHASH,
            function () {
                return str_random(40);
            }
        );
    }
}
