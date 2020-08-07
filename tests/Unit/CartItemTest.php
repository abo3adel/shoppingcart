<?php

namespace Abo3adel\ShoppingCart\Tests\Unit;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\Car;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class CartItemTest extends TestCase
{
    public function testTableName()
    {
        $this->assertSame(
            'cart_items' . Cart::tbAddon(),
            (new CartItem())->getTable()
        );
    }

    public function testUserConfiguredCasts()
    {
        // opt1 is default to size
        // changing opt1 name here won`t effect
        // migration files which run before test method
        Config::set('shoppingcart.casts.opt1', 'array');

        $item = factory(CartItem::class)->create([
            'size' => json_encode([])
        ]);

        $this->assertIsArray($item->size);

        // update value and check if it renders to string when saving
        $item->size = ['www'];
        $item->update();

        $this->assertDatabaseHas('cart_items' . Cart::tbAddon(), [
            'size' => json_encode(['www'])
        ]);
    }

    public function testItHasMorphRelation()
    {
        $item = factory(CartItem::class)->create([
            'buyable_type' => Car::class,
        ]);

        $this->assertSame(
            Car::class,
            $item->buyable_type
        );
    }

    public function testItHaveSubTotal()
    {
        $craft = factory(SpaceCraft::class)->create([
            'discount' => 50.5,
            'price' => 120,
        ]);
        $item = Cart::add($craft, 5);

        $this->assertSame(
            297.0,
            $item->sub_total
        );
    }

    public function testItCanBeIncremented()
    {
        $model = factory(Car::class)->create();
        $item = Cart::add($model, 7);

        $item = $item->increments(5);

        $this->assertSame(
            12,
            $item->qty
        );
    }

    public function testItCanBeDecremented()
    {
        $model = factory(Car::class)->create();
        $item = Cart::add($model, 7);

        $item = $item->decrements(5);

        $this->assertSame(
            2,
            $item->qty
        );
    }
}
