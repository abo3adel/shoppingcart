<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Listeners\SaveCartItemsIntoDataBase;
use Abo3adel\ShoppingCart\Providers\EventServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
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
        $this->mergeConfigFrom(__DIR__ . '/../config/shoppingcart.php', 'shoppingcart');
        $this->publishThings();
        // $this->loadViewsFrom(__DIR__.'/resources/views', 'shopping-cart');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        // $this->registerRoutes();

        Event::listen([
            Login::class,
            Registered::class
        ], SaveCartItemsIntoDataBase::class);
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

        // $this->app->register(EventServiceProvider::class);
    }

    public function publishThings()
    {
        if ($this->app->runningInConsole() && !file_exists(config_path('shoppingcart.php'))) {
            $this->publishes([
                __DIR__ . '/../config/shoppingcart.php' => config_path('shoppingcart.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../../database/migrations/create_cart_items_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_cart_items_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }
    }
}
