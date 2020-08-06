<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
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
