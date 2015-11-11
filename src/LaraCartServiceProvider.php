<?php namespace LukePOLO\LaraCart;

use Illuminate\Support\ServiceProvider;
use LukePOLO\LaraCart\Contracts\LaraCartContract;

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
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            LaraCartContract::class,
            LaraCart::class
        );

        $this->app->singleton(
            'laracart',
            function($app) {
                return $app->make(LaraCartContract::class);
            }
        );

        $this->app->bind(
            LaraCart::HASH,
            function($app, $data) {
                return md5(json_encode($data));
            }
        );

        $this->app->bind(
            LaraCart::RANHASH,
            function() {
                return str_random(40);
            }
        );
    }
}
