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

        /*
         * Publish migration if not published yet
         */
        if (!$this->migrationHasAlreadyBeenPublished()) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__ . '/../resources/migrations/add_cart_session_id_to_users_table.php.stub' => database_path('migrations/' . $timestamp . '_add_cart_session_id_to_users_table.php'),
            ], 'migrations');
        }
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

    /**
     * @return bool
     */
    protected function migrationHasAlreadyBeenPublished()
    {
        $files = glob(database_path('/migrations/*_add_cart_session_id_to_users_table.php'));
        return count($files) > 0;
    }
}
