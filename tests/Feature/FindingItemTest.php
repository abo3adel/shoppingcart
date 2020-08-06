<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Car;
use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class FindingItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanFindItemById()
    {
        $this->createItem(4);
        $model = factory(SpaceCraft::class)->create([
            'price' => 225.24
        ]);
        $item = Cart::add($model, 66);
        $this->createItem(2);

        $found = Cart::find($item->id);
        
        $this->assertSame(
            $item->qty,
            $found->qty
        );
        $this->assertSame(
            $item->buyable_type,
            $found->buyable_type
        );
    }

    public function testGuestCanFindItemByBuyable()
    {
        $this->createItem(4);
        $model = factory(SpaceCraft::class)->create([
            'price' => 225.24
        ]);
        $item = Cart::add($model, 88, 931);
        $this->createItem(2);

        $found = Cart::find($model->id, SpaceCraft::class);
        
        $this->assertSame(
            $item->qty,
            $found->qty
        );

        $this->assertNull(Cart::find(25));
        $this->assertNull(Cart::find(2, Car::class));
    }

    public function testUserCanFindItemById()
    {
        $this->signIn();

        $this->testGuestCanFindItemById();
    }

    public function testUserCanFindItemByBuyable()
    {
        $this->signIn();

        $this->testGuestCanFindItemByBuyable();
    }
}
