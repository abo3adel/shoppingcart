<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Listeners\SaveCartItemsIntoDataBase;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;

class CartItemIsMergedAfterLoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testItemsWillBeSavedToDBAfterLogin()
    {
        $item = $this->createItem(5);
        $wish = $this->createItem(7, [], 'wish');

        $this->assertCount(6, Cart::instance()->content());
        $this->assertCount(8, Cart::instance('wish')->content());

        $this->assertGreaterThan(7, session(Cart::sessionName()));
        
        // Cart::afterLogin($this->signIn());
        // (new SaveCartItemsIntoDataBase())->handle(
        //     new Login('web', $this->signIn(), false)
        // );
        auth()->guard()->login($this->signIn());

        $this->assertEmpty(session(Cart::sessionName()));
        $this->assertCount(6, Cart::instance()->content());
        $this->assertCount(8, Cart::instance('wish')->content());
        
        $this->assertDatabaseHas(Cart::tbName(), [
            'price' => $item->price,
            'instance' => 'default'
        ]);
    }

    public function testItWillMergeNewItemsWithOldItems()
    {
        $user = $this->signIn();
        // (new SaveCartItemsIntoDataBase())->handle(
        //     new Login('web', $user, false)
        // );
        auth()->guard()->login($user);

        $item = $this->createItem(4);
        $wish = $this->createItem(9, [], 'wish');
        $this->assertDatabaseHas(Cart::tbName(), [
            'instance' => 'default',
            'price' => $item->price
        ]);

        auth()->guard()->logout();
        $this->assertFalse(auth()->check());

        $this->createItem(11);
        $this->createItem(3, [], 'wish');

        // Cart::afterLogin($this->signIn($user));
        // (new SaveCartItemsIntoDataBase())->handle(
        //     new Login('web', $this->signIn($user), false)
        // );
        auth()->guard()->login($this->signIn($user));

        $this->assertCount(17, Cart::instance()->content());
        $this->assertCount(14, Cart::instance('wish')->content());
        
        $this->assertDatabaseHas(Cart::tbName(), [
            'instance' => 'default',
            'price' => $item->price
        ]);
        $this->assertDatabaseHas(Cart::tbName(), [
            'instance' => 'wish',
            'price' => $wish->price
        ]);
    }

    public function testItWillUpdateAlreadySavedItemsIfChanged()
    {
        $user = $this->signIn();
        // (new SaveCartItemsIntoDataBase())->handle(
        //     new Login('web', $user, false)
        // );
        auth()->guard()->login($user);

        $this->createItem(3);
        $model = factory(SpaceCraft::class)->create();
        $item = Cart::add(
            $model,
            25,
            70,
            9,
            ['alive' => true]
        );
        $this->assertCount(5, Cart::instance()->content());

        auth()->guard()->logout();
        Cart::resetUser();

        // login with another user
        // (new SaveCartItemsIntoDataBase())->handle(
        //     new Login('web', $this->signIn(), false)
        // );
        auth()->guard()->login($this->signIn());

        // Cart::afterLogin($this->signIn());
        $this->createItem();
        $this->assertCount(2, Cart::content());
        auth()->guard()->logout();
        Cart::resetUser();

        $this->createItem(5, [], 'wish');
        Cart::instance()->add($model, 50, 30, 15, $item->options);
        $this->assertCount(1, Cart::instance()->content());

        // Cart::afterLogin($this->signIn($user));
        // (new SaveCartItemsIntoDataBase())->handle(
        //     new Login('web', $this->signIn($user), false)
        // );
        auth()->guard()->login($this->signIn($user));

        $this->assertCount(5, Cart::instance()->content());
        $item = Cart::find($item->id);
        $this->assertSame(
            $model->id,
            (int) $item->buyable_id
        );
        $this->assertSame(50, $item->qty);
        $this->assertSame(15, $item->{Cart::sopt()});
        $this->assertArrayHasKey('alive', $item->options);
    }
}
