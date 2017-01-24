<?php

namespace LukePOLO\LaraCart;

use Illuminate\Support\ServiceProvider;

/**
 * Class LaraCartServiceProvider.
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
        $this->publishes([
            __DIR__.'/config/laracart.php' => config_path('laracart.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/config/laracart.php',
            'laracart'
        );

        if (!$this->migrationHasAlreadyBeenPublished()) {
            $this->publishes([
                __DIR__.'/database/migrations/add_cart_session_id_to_users_table.php.stub' => database_path('migrations/'.date('Y_m_d_His').'_add_cart_session_id_to_users_table.php'),
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
        });

        $this->app->singleton(LaraCart::HASH, function () {
            return new LaraCartHasher();
        });

        $this->app->bind(
            LaraCart::RANHASH,
            function () {
                return str_random(40);
            }
        );
    }

    /**
     * Checks to see if the migration has already been published.
     *
     * @return bool
     */
    protected function migrationHasAlreadyBeenPublished()
    {
        $files = glob(database_path('migrations/*_add_cart_session_id_to_users_table.php'));

        return count($files) > 0;
    }
}
