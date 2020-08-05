<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;

class RetrivingContentTest extends TestCase
{
    public function testGuestCanGetCartContent()
    {
        $item = $this->createItem(4, [
            'price' => 5.5
        ]);

        $wish = $this->createItem(6, [
            'price' => 22.3
        ], 'wish');

        $this->assertCount(5, Cart::instance()->content());
        $this->assertCount(7, Cart::instance('wish')->content());

        $found = Cart::instance()->content()
            ->contains(function ($val) use ($item) {
                return $val->id === $item->id;
            });
        $this->assertTrue($found);
    }

    public function testUserCanGetCartContent()
    {
        $this->signIn();

        $this->testGuestCanGetCartContent();
    }
}
