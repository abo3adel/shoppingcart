<?php

namespace Abo3adel\ShoppingCart;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ShoppingCartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ShoppingCart.php', 'shopping-cart');
        $this->publishThings();
        // $this->loadViewsFrom(__DIR__.'/resources/views', 'shopping-cart');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        });
    }

    /**
    * Get the Blogg route group configuration array.
    *
    * @return array
    */
    private function routeConfiguration()
    {
        return [
            'namespace'  => "Abo3adel\ShoppingCart\Http\Controllers",
            'middleware' => 'api',
            'prefix'     => 'api'
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register facade
        $this->app->singleton('shopping-cart', function () {
            return new ShoppingCartCtrl;
        });
    }

    public function publishThings(){
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ShoppingCart.php' => config_path('ShoppingCart.php'),
            ], 'config');
        }
    }
}