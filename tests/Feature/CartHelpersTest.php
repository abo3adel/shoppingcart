<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class CartHelpersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanCalculateItemsTotal()
    {
        Cart::instance()->add(
            factory(SpaceCraft::class)->create(['price' => 260]),
            5
        );
        Cart::instance()->add(
            factory(SpaceCraft::class)->create(['price' => 190]),
            7
        );

        Cart::instance('wish')->add(
            factory(SpaceCraft::class)->create(['price' => 340]),
            3
        );

        Cart::instance('wish')->add(
            factory(SpaceCraft::class)->create(['price' => 70]),
            25
        );

        $this->assertSame((float)2630, Cart::instance()->total());
        $this->assertSame((float)2770, Cart::instance('wish')->total());
    }

    public function testUserCanGetItemTotal()
    {
        $this->signIn();

        $this->testGuestCanCalculateItemsTotal();
    }

    public function testGuestCanCalculateItemTotalPrice()
    {
        $this->createItemWithData(1, 9, 5);
        $this->createItemWithData(4, 25, 7);

        $this->createItemWithData(1, 6.5, 10, 'wish');
        $this->createItemWithData(3, 14, 3, 'wish');

        $this->assertSame(109.0, Cart::instance()->totalPrice());
        $this->assertSame(48.5, Cart::instance('wish')->totalPrice());
    }

    public function testUserCanGetItemTotalPrice()
    {
        $this->signIn();

        $this->testGuestCanCalculateItemTotalPrice();
    }

    public function testGuestCanCalculateTotalQtyOnly()
    {
        $this->createItemWithData(1, 9, 5);
        $this->createItemWithData(4, 25, 70);

        $this->createItemWithData(1, 6.5, 25, 'wish');
        $this->createItemWithData(3, 14, 120, 'wish');

        $this->assertSame(285, Cart::instance()->totalQty());
        $this->assertSame(385, Cart::instance('wish')->totalQty());
    }

    public function testUserCanGetTotalQtyOnly()
    {
        $this->signIn();

        $this->testGuestCanCalculateTotalQtyOnly();
    }

    public function testGuestCanCalculateCartTotalAfterTax()
    {
        Cart::setTax(25);
        
        // total (default) => 2630
        // total (wish) => 2770
        $this->testGuestCanCalculateItemsTotal();

        $this->assertSame(1972.5, Cart::instance()->subTotal());
        $this->assertSame(
            2077.5,
            Cart::instance('wish')->subTotal()
        );
    }

    private function createItemWithData(
        int $count = 1,
        float $price,
        int $qty,
        ?string $instance = null
    ): CartItem {
        foreach (range(1, $count) as $i) {
            $item = Cart::instance($instance)->add(
                factory(SpaceCraft::class)->create(['price' => $price]),
                $qty
            );
        }

        return $item;
    }
}
