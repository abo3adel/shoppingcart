<?php

namespace Abo3adel\ShoppingCart\Tests;

use Abo3adel\ShoppingCart\ShoppingCartServiceProvider;
use Illuminate\Foundation\Auth\User;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setup() : void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->artisan('migrate', ['--database' => 'testing']);
        
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
        $this->loadLaravelMigrations(['--database' => 'testing']);
        
        $this->withFactories(__DIR__.'/../src/database/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // run migrations
        // require_once __DIR__ . '/../src/database/migrations/create_cart_items_table.php.stub';
        // (new \CreateCartItemsTable)->up();
    }

    protected function getPackageProviders($app)
    {
        return [ShoppingCartServiceProvider::class];
    }

    public function signIn(?User $user = null, array $attrs = []): User
    {
        if (null === $user) {
            $user = factory(User::class)->create($attrs);
        }

        $this->actingAs($user);

        return $user;
    }
}
