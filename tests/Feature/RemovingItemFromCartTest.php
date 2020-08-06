<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\Events\CartItemRemoved;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

class RemovingItemFromCartTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanRemoveItemByItemId()
    {
        $item = $this->createItem(5, ['price' => 25]);
        $wish = $this->createItem(4, ['price' => 220], 'wish');
        $this->createItem(3);
        $this->createItem(11, [], 'wish');

        $this->assertCount(17, Cart::instance('wish')->content());
        $this->assertCount(10, Cart::instance()->content());
        $this->assertNotNull(Cart::find($item->id));

        Cart::delete($item->id);
        $this->assertNull(Cart::find($item->id));
        $this->assertCount(9, Cart::content());
        $this->assertCount(17, Cart::instance('wish')->content());

        Cart::instance('wish')->delete($wish->id);
        $this->assertCount(9, Cart::instance()->content());
        $this->assertCount(16, Cart::instance('wish')->content());
    }

    public function testUserCanRemoveItem()
    {
        $this->signIn();

        $this->testGuestCanRemoveItemByItemId();
    }

    public function testGuestRemovingWillFireEvent()
    {
        Event::fake();

        $this->testGuestCanRemoveItemByItemId();

        Event::assertDispatched(CartItemRemoved::class, function ($ev) {
            return $ev->item->price === (float)25;
        });
        Event::assertDispatched(CartItemRemoved::class, function ($ev) {
            return $ev->item->price === (float)220;
        });
    }

    public function testUserRemovingWillFireEvent()
    {
        Event::fake();

        $this->testUserCanRemoveItem();

        Event::assertDispatched(CartItemRemoved::class, function ($ev) {
            return $ev->item->price === (float)25;
        });
        Event::assertDispatched(CartItemRemoved::class, function ($ev) {
            return $ev->item->price === (float)220;
        });
    }
}
