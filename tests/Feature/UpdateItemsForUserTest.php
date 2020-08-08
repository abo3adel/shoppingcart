<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateItemsForUserTest extends TestCase
{
    use RefreshDatabase;

    public function testItCanAddItemsForUser()
    {
        $user = $this->signIn();
        $this->createItem(5);
        $this->assertCount(6, Cart::content());

        Cart::resetUser();

        $anotherUser = $this->signIn();
        $this->assertCount(0, Cart::content());
        $this->assertCount(
            6,
            Cart::forUser($user)->instance()->content()
        );
        
        // user here is $user
        Cart::add(
            factory(SpaceCraft::class)->create(),
            25
        );
        $this->assertCount(
            7,
            Cart::forUser($user)->instance()->content()
        );
    }
}
