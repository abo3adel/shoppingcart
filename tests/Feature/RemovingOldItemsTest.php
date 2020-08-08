<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class RemovingOldItemsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testItWillDeleteOldItems()
    {
        $this->signIn();

        $before15Days = Carbon::now()->subDays(16);

        $this->createItem(9);
        $this->createItem(14, [], 'wish');
        foreach (range(0, 6) as $i) {
            $model = factory(SpaceCraft::class)->create();
            $item = factory(CartItem::class)->create([
                'buyable_id' => $model->id,
                'buyable_type' => SpaceCraft::class,
                'user_id' => auth()->id(),
                'updated_at' => $before15Days,
            ]);
        }

        $this->assertCount(17, Cart::instance()->content());
        $this->assertCount(15, Cart::instance('wish')->content());

        $deleted = Cart::removeOldCartItems(15);

        $this->assertSame(7, $deleted);
        $this->assertCount(10, Cart::instance()->content());
        $this->assertCount(15, Cart::instance('wish')->content());
    }
}
