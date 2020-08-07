<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class CartItemIsMergedAfterLoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testItemsWillBeSavedToDBAfterLogin()
    {
        $item = $this->createItem(5);
        $wish = $this->createItem(7, [], 'wish');

        $this->assertCount(6, Cart::instance()->content());
        $this->assertCount(8, Cart::instance('wish')->content());

        $this->assertGreaterThan(7, session(Cart::sessionName()));
        $this->signIn();
        Cart::afterLogin();

        $this->assertEmpty(session(Cart::sessionName()));
        $this->assertCount(6, Cart::instance()->content());
        $this->assertCount(8, Cart::instance('wish')->content());
        
        $this->assertDatabaseHas(Cart::tbName(), [
            'price' => $item->price,
            'instance' => 'default'
        ]);
    }
}
