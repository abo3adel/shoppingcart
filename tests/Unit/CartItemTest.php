<?php

namespace Abo3adel\ShoppingCart\Tests\Unit;

use Abo3adel\ShoppingCart\Car;
use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
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

        $this->assertInstanceOf(
            Car::class,
            $item->buyable
        );
    }
}
