<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CartHasItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanCheckIfCartHasItem()
    {
        $this->createItem(3);
        $model = factory(SpaceCraft::class)->create();
        $item = Cart::add($model, 26, 180, 19);
        $this->createItem(7);

        $this->assertFalse(Cart::has(15));
        $this->assertTrue(Cart::has($item->id));
    }

    public function testGuestCanCheckIfCartHasItemByBuyableId()
    {
        $this->createItem(3);
        $model = factory(SpaceCraft::class)->create();
        Cart::add($model, 93);
        $this->createItem(7);

        $this->assertFalse(Cart::has(25, SpaceCraft::class));
        $this->assertTrue(Cart::has($model->id, SpaceCraft::class));
    }

    public function testUserCanCheckIfItemExists()
    {
        $this->signIn();
        $this->testGuestCanCheckIfCartHasItem();
    }

    public function testUserCanCheckIfItemExistsByBuyable()
    {
        $this->signIn();
        $this->testGuestCanCheckIfCartHasItemByBuyableId();
    }
}
