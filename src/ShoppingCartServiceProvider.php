<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Console\Commands\RemoveOldItemsCommand;
use Abo3adel\ShoppingCart\Listeners\SaveCartItemsIntoDataBase;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        Event::listen([
            Login::class,
            Registered::class
        ], SaveCartItemsIntoDataBase::class);

        if ($this->app->runningInConsole()) {
            $this->publishThings();

            $this->commands([
                RemoveOldItemsCommand::class,
            ]);
        }
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

    public function publishThings()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ShoppingCart.php' => config_path('shoppingcart.php'),

                // publish migrations
                __DIR__ . '/../database/migrations/2020_08_04_205055_create_cart_items_table.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_cart_items_table.php'),
            ], 'shoppingcart-all');
        }
    }
}
