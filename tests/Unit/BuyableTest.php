<?php

namespace Abo3adel\ShoppingCart\Tests\Unit;

use Abo3adel\ShoppingCart\Car;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\SpaceCraft;
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
}
