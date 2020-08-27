<?php

namespace Skyyouare\Gii;

use Illuminate\Support\ServiceProvider;

class GiiServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'skyyouare');
        $this->loadViewsFrom(__DIR__.'/views', 'gii_views');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/gii.php', 'gii');

        // Register the service the package provides.
        $this->app->singleton('gii', function ($app) {
            return new gii;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['gii'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/gii.php' => config_path('gii.php'),
        ], 'gii.config');

        // Publishing assets.
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('gii_assets'),
        ], 'gii.views');

        // Publishing images.
        $this->publishes([
            __DIR__.'/../resources/images' =>  public_path('images'),
        ], 'gii.images');

        // Publishing js.
        $this->publishes([
            __DIR__.'/../resources/js' =>  base_path('resources/js'),
        ], 'gii.js');

        // Publishing blade.
        $this->publishes([
            __DIR__.'/../resources/blade' =>  base_path('resources/views'),
        ], 'gii.blade');

        // Publishing sass.
        $this->publishes([
            __DIR__.'/../resources/sass' =>  base_path('resources/sass'),
        ], 'gii.sass');

        //publishing controller
        $this->publishes([
            __DIR__.'/../resources/controllers' =>  base_path('app/Http/Controllers'),
        ], 'gii.controller');
        //publishing request
        $this->publishes([
            __DIR__.'/../resources/Requests' =>  base_path('app/Http/Requests'),
        ], 'gii.request');
        //publishing route
        $this->publishes([
            __DIR__.'/../resources/routes' =>  base_path('routes'),
        ], 'gii.route');
        //exception route
        $this->publishes([
            __DIR__.'/../resources/Exceptions' =>  base_path('app/Exceptions'),
        ], 'gii.exception');
        //publishing provider
        $this->publishes([
            __DIR__.'/../resources/Providers' =>  base_path('app/Providers'),
        ], 'gii.provider');
        // Registering package commands.
        // $this->commands([]);
    }
}
