<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CartHelpersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanCalculateItemsTotal()
    {
        Cart::instance()->add(
            factory(SpaceCraft::class)->create(['price' => 3]),
            5
        );
        Cart::instance()->add(
            factory(SpaceCraft::class)->create(['price' => 4]),
            3
        );

        Cart::instance('wish')->add(
            factory(SpaceCraft::class)->create(['price' => 4]),
            7
        );

        Cart::instance('wish')->add(
            factory(SpaceCraft::class)->create(['price' => 3]),
            2
        );

        $this->assertSame((float)27, Cart::instance()->total());
        $this->assertSame((float)34, Cart::instance('wish')->total());
    }

    public function testUserCanGetItemTotal()
    {
        $this->signIn();

        $this->testGuestCanCalculateItemsTotal();
    }
}
