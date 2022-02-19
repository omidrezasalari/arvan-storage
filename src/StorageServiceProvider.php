<?php

namespace Omidrezasalari\ArvanStorage;

use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . "/config/arvan_config.php", "arvan_config");

        $this->app->singleton(ArvanCloudFacade::class, config('arvan_config.bindClass'));

    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/arvan_config.php' => config_path('arvan_config.php')
        ], 'arvan_config');
    }
}
