<?php

namespace Abo3adel\ShoppingCart\Tests\Unit;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\ShoppingCartCtrl;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ShoppingCartCtrlTest extends TestCase
{
    use RefreshDatabase;

    public function testGettingCurrentInstance()
    {
        $this->assertNull(Cart::getInstance());
    }

    public function testSettingInstance()
    {
        $this->assertInstanceOf(ShoppingCartCtrl::class, Cart::instance('www'));

        $this->assertEquals('www', Cart::getInstance());
    }
}
