<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\SpaceCraft;
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
        $item = Cart::add($model, 5);
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
        $item = Cart::add($model, 5, 22);
        $this->createItem(2);

        $found = Cart::find($item->buyable_id, SpaceCraft::class);
        
        $this->assertSame(
            $item->qty,
            $found->qty
        );

        $this->assertNull(Cart::find(56345));
    }
}
