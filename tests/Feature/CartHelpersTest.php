<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\Car;
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

        // it will format
        $this->assertSame(
            \number_format(2630, 2),
            Cart::instance()->total(true)
        );
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

        // it will format
        $this->assertSame(
            \number_format(1972.5, 2),
            Cart::instance()->subTotal(true)
        );
    }

    public function testIncrementItemQty()
    {
        $buyable = factory(Car::class)->create([
            'price' => 25,
            'qty' => 40
        ]);
        $item = $buyable->addToCart(10);
        Cart::increments($item->id, 5);

        $this->assertSame(
            15,
            Cart::find($item->id)->qty
        );

        // incrementing greater than buyable qty
        $this->assertNull(Cart::increments($item->id, 26));

        if (auth()->check()) {
            $this->assertDatabaseHas(Cart::tbName(), [
                'qty' => 15,
                'price' => 25
            ]);
        }
    }

    public function testUserCanIncrementQty()
    {
        $this->signIn();
        $this->testIncrementItemQty();
    }

    public function testItCanDecrementQty()
    {
        $item = $this->createItemWithData(3, 25, 10);
        Cart::decrements($item->id, 4);

        $this->assertSame(
            6,
            Cart::find($item->id)->qty
        );

        if (auth()->check()) {
            $this->assertDatabaseHas(Cart::tbName(), [
                'qty' => 6,
                'price' => 25
            ]);
        }
    }

    public function testUserCanDecrementQty()
    {
        $this->signIn();
        $this->testItCanDecrementQty();
    }

    public function testItWillNotIncrementIfNumberIsGreaterThanBuyableQty()
    {
        $buyable = factory(Car::class)->create([
            'qty' => 15
        ]);
        $item = Cart::add($buyable, 4);

        $this->assertNull(Cart::increments($item->id, 16));

        if (auth()->check()) {
            $this->assertDatabaseHas(Cart::tbName(), [
                'qty' => 4,
                'buyable_id' => $buyable->id
            ]);
        }
    }

    public function testItWillNotIncrementForUser()
    {
        $this->signIn();

        $this->testItWillNotIncrementIfNumberIsGreaterThanBuyableQty();
    }

    public function testItWillNotDecrementIfNumberIsGreaterThanItemQty()
    {
        $buyable = factory(Car::class)->create([
            'qty' => 15
        ]);
        $item = Cart::add($buyable, 4);

        $this->assertNull(Cart::decrements($item->id, 5));

        if (auth()->check()) {
            $this->assertDatabaseHas(Cart::tbName(), [
                'qty' => 4,
                'buyable_id' => $buyable->id
            ]);
        }
    }

    public function testItWillNotDecrementForUser()
    {
        $this->signIn();
        $this->testItWillNotDecrementIfNumberIsGreaterThanItemQty();
    }


    private function createItemWithData(
        int $count = 1,
        float $price,
        int $qty,
        ?string $instance = null
    ): CartItem {
        foreach (range(1, $count) as $i) {
            $item = Cart::instance($instance)->add(
                factory(Car::class)->create(['price' => $price]),
                $qty
            );
        }

        return $item;
    }
}
