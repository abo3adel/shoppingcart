<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\Car;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BuyableStockAmountTest extends TestCase
{
    use RefreshDatabase;

    public function testItemsWithOutOfStockBuyableWillbeRemoved()
    {
        $buyable = factory(Car::class)->create([
            'qty' => 10
        ]);
        $item = Cart::add($buyable, 5);
        $buyable->update(['qty' => 0]);
        Cart::add(
            factory(Car::class)->create([
                'qty' => 25
            ]),
            10
        );
        $this->assertCount(2, Cart::content());

        [$outOfStockItems,] = Cart::checkBuyableStockAmount();
        $this->assertCount(1, Cart::content());
        $this->assertSame(
            $item->id,
            $outOfStockItems[0]->id
        );
    }

    public function testItWillUpdateAmountIfExceededBuyableQty()
    {
        $buyable = factory(Car::class)->create([
            'qty' => 25
        ]);
        $item = Cart::add($buyable, 10);
        $buyable->update(['qty' => 6]);
        Cart::add(
            factory(Car::class)->create([
                'qty' => 25
            ]),
            10
        );
        $this->assertCount(2, Cart::content());

        [, $amountErrors] = Cart::checkBuyableStockAmount();

        $this->assertCount(2, Cart::content());
        $item = Cart::find($item->id);
        $this->assertSame(
            4,
            $item->qty
        );
        $this->assertSame(10, $amountErrors[0]->from);
        $this->assertDatabaseHas($buyable->getTable(), [
            'id' => $buyable->id,
            'qty' => 6
        ]);

        if (auth()->check()) {
            $this->assertDatabaseHas(Cart::tbName(), [
                'buyable_id' => $buyable->id,
                'qty' => 4
            ]);
        }
    }

    public function testItWillWorkAfterLogIn()
    {
        $this->signIn();
        $this->testItemsWithOutOfStockBuyableWillbeRemoved();
        $this->assertEmpty(session(Cart::sessionName()));
    }

    public function testItWillUpdateAmountAfterLogIn()
    {
        $this->signIn();
        $this->testItWillUpdateAmountIfExceededBuyableQty();
    }

    public function testItWillUpdateBuyableObject()
    {
        $this->createItem(6);
        $this->createItem(4, [], 'wish');
        $buyable = factory(SpaceCraft::class)->create([
            'price' => 553
        ]);
        $item = Cart::instance()->add($buyable, 23);
        $item->load('buyable');
        
        $buyable->update(['price' => 432]);
        $this->assertSame(553, (int)$item->buyable->price);

        Cart::refreshItemsBuyableObjects();
        $item = Cart::find($item->id);
        $this->assertSame(432.0, (float) $item->buyable->price);
        
        $this->assertCount(5, Cart::instance('wish')->content());
        $this->assertCount(8, Cart::instance()->content());
    }
}
