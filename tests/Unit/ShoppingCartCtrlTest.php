<?php

namespace Abo3adel\ShoppingCart\Tests\Unit;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\ShoppingCartCtrl;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ShoppingCartCtrlTest extends TestCase
{
    use RefreshDatabase;

    public function testGettingCurrentInstance()
    {
        $this->assertSame(
            config('shoppingcart.defaultInstance'),
            Cart::getInstance()
        );
    }

    public function testSettingInstance()
    {
        $this->assertInstanceOf(ShoppingCartCtrl::class, Cart::instance('www'));

        $this->assertEquals('www', Cart::getInstance());
    }

    public function testItWillSetDefaultInstance()
    {
        $this->assertSame('default', Cart::getInstance());
    }

    public function testSettingSessionArray()
    {
        Cart::getInstance();
        $this->assertIsArray(
            session(config('shoppingcart.session_name'))
        );
    }

    public function testItCanCreateCartItem()
    {
        $item = factory(CartItem::class)->create([
            'price' => 250
        ]);

        $this->assertSame((float)250, $item->price);
    }

    public function testItCanChangeTaxAtRunTime()
    {
        Cart::setTax(50);

        $this->assertSame(50, Cart::getTax());
    }
}
