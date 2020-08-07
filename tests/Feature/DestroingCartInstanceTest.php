<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\Events\CartInstanceDestroyed;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;

class DestroingCartInstanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanDestroyCartIntance()
    {
        $this->createItem(4);
        $this->createItem(3, [], 'wish');
        $this->createItem(5, [], 'cmp');

        $this->assertCount(5, Cart::instance()->content());
        $this->assertCount(4, Cart::instance('wish')->content());
        $this->assertCount(6, Cart::instance('cmp')->content());

        Cart::instance('wish')->destroy();

        $this->assertCount(5, Cart::instance()->content());
        $this->assertCount(0, Cart::instance('wish')->content());
        $this->assertCount(6, Cart::instance('cmp')->content());
    }

    public function testUserCanDestroyInstance()
    {
        $this->signIn();

        $this->testGuestCanDestroyCartIntance();

        $this->assertDatabaseMissing(Cart::tbName(), [
            'instance' => 'wish'
        ]);
        $this->assertDatabaseHas(Cart::tbName(), [
            'instance' => 'cmp'
        ]);
    }

    public function testDestroingInstanceWillNotAffectAnotherUser()
    {
        $user = $this->signIn();
        $this->createItem(3);
        $this->createItem(6, [], 'wish');

        // signIn with another user
        $anotherUser = $this->signIn();
        $this->createItem(5);
        $this->createItem(4, [], 'wish');
        Cart::instance('wish')->destroy();

        // signIn again with original user
        $user = $this->signIn($user);
        $this->assertDatabaseHas(Cart::tbName(), [
            'user_id' => $user->id,
            'instance' => 'wish'
        ]);
    }

    public function testDestroyingInstanceWillfireEvent()
    {
        Event::fake();

        $this->createItem(8, [], 'wish');

        Cart::instance('wish')->destroy();

        Event::assertDispatched(
            CartInstanceDestroyed::class,
            function ($ev) {
                return $ev->instance === 'wish';
            }
        );
    }

    public function testUserDestroyingInstanceWillFireEvent()
    {
        $this->signIn();

        $this->testDestroyingInstanceWillfireEvent();
    }
}
