<?php namespace LukePOLO\LaraCart;

use Illuminate\Support\ServiceProvider;

/**
 * Class LaraCartServiceProvider
 *
 * @package LukePOLO\LaraCart
 */
class LaraCartServiceProvider extends ServiceProvider {

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/laracart.php' => config_path('laracart.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['laracart'] = $this->app->share(function($app)
        {
            return new Cart($app['session']);
        });
    }
}