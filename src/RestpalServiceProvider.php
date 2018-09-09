<?php

/**
 * Restpal 
 * 
 * @author Damian Balandowski (balandowski@icloud.com)
 */

namespace damianbal\Restpal;

use Illuminate\Support\ServiceProvider;

use damianbal\Restpal\RestpalConfiguration;

class RestpalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // routes
        $this->loadRoutesFrom(__DIR__ . "/../routes/routes.php");

        // config
        $this->publishes([
            __DIR__ . '/../config/restpal.php' => config_path('restpal.php')
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(RestpalConfiguration::class, function() {
            return new RestpalConfiguration();
        });
    }
}
