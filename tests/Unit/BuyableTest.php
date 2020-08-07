<?php

namespace Abo3adel\ShoppingCart\Tests\Unit;

use Abo3adel\ShoppingCart\Tests\Model\Car;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class BuyableTest extends TestCase
{
    public function testItCanHaveManyCartItems()
    {
        $car = factory(Car::class)->create();
        $car->items()->saveMany(
            factory(CartItem::class, 4)->make()
        );

        $this->assertCount(4, $car->items);

        $spc = factory(SpaceCraft::class)->create();
        $spc->items()->saveMany(
            factory(CartItem::class, 2)->make()
        );

        $this->assertCount(2, $spc->items);
    }

    public function testItCanBeAddedToCart()
    {
        $buyable = factory(SpaceCraft::class)->create();
        $item = $buyable->addToCart(25);

        $this->assertSame(
            $buyable->id,
            $item->buyable_id
        );
        $this->assertSame(25, $item->qty);
    }
}
