<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Events\CartItemAdded;
use Abo3adel\ShoppingCart\Exceptions\InvalidModelException;
use Abo3adel\ShoppingCart\Exceptions\ItemAlreadyExistsException;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class AddingItemsToCartTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanAddItemWithMinimalArgs()
    {
        $buyable = factory(SpaceCraft::class)->create([
            'price' => 553,
            'discount' => 50
        ]);

        $item = Cart::add($buyable, 22);
        $this->assertSame((float)276.5, $item->price);
        $this->assertSame(22, $item->qty);
    }

    public function testGuestCanAddItemWithOpt1AndOpt2()
    {
        $buyable = factory(SpaceCraft::class)->create([
            'price' => 15056,
            'discount' => 77,
        ]);

        $item = Cart::add($buyable, 38, 6);
        $this->assertSame((float)3462.88, $item->price);
        $this->assertSame(38, $item->qty);
        $this->assertSame(6, $item->{Cart::fopt()});
        $this->assertNull($item->{Cart::sopt()});

        $buyable = factory(SpaceCraft::class)->create([
            'price' => 3462.88
        ]);
        $item = Cart::add($buyable, 70, 4, 13);
        $this->assertSame(70, $item->qty);
        $this->assertSame(4, $item->{Cart::fopt()});
        $this->assertSame(13, $item->{Cart::sopt()});
    }

    public function testGuestCanAddItemWithAllArgs()
    {
        $buyable = factory(SpaceCraft::class)->create([
            'price' => 167
        ]);

        $item = Cart::add($buyable, 66, 5, 2, [
            'name' => 'asd'
        ]);
        $this->assertSame((float)167, $item->price);
        $this->assertSame(66, $item->qty);
        $this->assertSame(5, $item->{Cart::fopt()});
        $this->assertSame(2, $item->{Cart::sopt()});
        $this->assertArrayHasKey('name', $item->options);
    }

    public function testGuestCanAddItemWithoutFirstOpt()
    {
        $buyable = factory(SpaceCraft::class)->create();

        Config::set('shoppingcart.opt1', null);
        $item = Cart::add($buyable, 99, [
            'name' => 'asd'
        ]);
        $this->assertSame(99, $item->qty);
        $this->assertArrayHasKey('name', $item->options);
    }

    public function testGuestCanAddItemWithoutSecondOpt()
    {
        $buyable = factory(SpaceCraft::class)->create();

        Config::set('shoppingcart.opt1', 'size');
        Config::set('shoppingcart.opt2', null);
        $item = Cart::add($buyable, 17, 96, [
            'name' => 'asd'
        ]);
        $this->assertSame(17, $item->qty);
        $this->assertSame(96, $item->{Cart::fopt()});
        $this->assertArrayHasKey('name', $item->options);
    }

    public function testGuestCanAddSameBuyableObjectInDefirentInstances()
    {
        $buyable = factory(SpaceCraft::class)->create();
        $defu = Cart::instance()->add($buyable, 250);

        $wish = Cart::instance('wishlist')->add($buyable, 60);

        $cmp = Cart::instance('compare')->add($buyable, 4);

        $this->assertSame('default', $defu->instance);
        $this->assertSame('wishlist', $wish->instance);
        $this->assertSame('compare', $cmp->instance);
    }

    public function testUserCanAddItems()
    {
        $this->signIn();

        $this->testGuestCanAddItemWithMinimalArgs();
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'price' => 276.5,
                'qty' => 22,
                'buyable_id' => 1,
            ]
        );

        $this->testGuestCanAddItemWithOpt1AndOpt2();
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'price' => 3462.88,
                'qty' => 70,
                Cart::fopt() => 4,
                Cart::sopt() => 13
            ]
        );

        $this->testGuestCanAddItemWithAllArgs();
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'price' => 167,
                'qty' => 66,
                Cart::fopt() => 5,
                Cart::sopt() => 2,
                'options' => json_encode([
                    'name' => 'asd'
                ])
            ]
        );

        $this->testGuestCanAddItemWithoutFirstOpt();
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'qty' => 99,
                'options' => json_encode([
                    'name' => 'asd'
                ]),
                'instance' => 'default',
            ]
        );

        $this->testGuestCanAddItemWithoutSecondOpt();
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'qty' => 17,
                Cart::fopt() => 96,
                'options' => json_encode([
                    'name' => 'asd'
                ]),
                'instance' => 'default',
            ]
        );

        $this->testGuestCanAddSameBuyableObjectInDefirentInstances();
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'qty' => 250,
                'instance' => 'default',
            ]
        );
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'qty' => 60,
                'instance' => 'wishlist',
            ]
        );
        $this->assertDatabaseHas(
            Cart::tbName(),
            [
                'qty' => 4,
                'instance' => 'compare',
            ]
        );
    }

    public function testAddingNewItemWillFireEvent()
    {
        Event::fake();

        $this->testGuestCanAddItemWithMinimalArgs();

        Event::assertDispatched(CartItemAdded::class, function ($ev) {
            return $ev->item->price === (float)276.5;
        });

        $this->signIn();
        $this->testGuestCanAddItemWithoutFirstOpt();
        Event::assertDispatched(CartItemAdded::class, function ($ev) {
            return $ev->item->qty === 99;
        });
    }

    public function testAddingBuyableModelWithoutRequiredAttributesWillThrowException()
    {
        $user = factory(User::class)->create();

        $this->expectException(InvalidModelException::class);

        Cart::add($user, 25);
    }

    public function testAddingSameBuyableWillThrowException()
    {
        $model = factory(SpaceCraft::class)->create();
        Cart::add($model, 25);

        $this->createItem(4);

        Cart::instance('wishlist')->add($model, 47);

        $this->expectException(ItemAlreadyExistsException::class);
        Cart::add($model, 3);
    }
}
