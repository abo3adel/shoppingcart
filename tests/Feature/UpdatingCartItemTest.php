<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\Exceptions\ItemNotFoundException;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class UpdatingCartItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanUpdateItem()
    {
        $item = $this->createItem(4);
        $wish = $this->createItem(3, [], 'wish');
        $cmp = $this->createItem(6, [], 'cmp');
        $this->createItem(5);
        $this->createItem(7, [], 'wish');
        $this->createItem(2, [], 'cmp');

        Cart::instance()->update($item->id, 25);
        // qty updated
        $this->assertSame(
            25,
            (Cart::find($item->id))->qty
        );
        // opt1 remains the same
        $this->assertSame(
            $item->{Cart::fopt()},
            (Cart::find($item->id))->{Cart::fopt()}
        );

        Cart::instance('wish')->update($wish->id, ['alive']);
        $this->assertSame(
            ['alive'],
            (Cart::find($wish->id))->options
        );
        $this->assertSame(
            $wish->qty,
            (Cart::find($wish->id))->qty
        );

        Cart::instance('cmp')->update($cmp->id, 4, 13, 77, ['still']);
        $this->assertSame(
            ['still'],
            (Cart::find($cmp->id))->options
        );
        $this->assertSame(
            4,
            (Cart::find($cmp->id))->qty
        );
        $this->assertSame(
            13,
            (Cart::find($cmp->id))->{Cart::fopt()}
        );
        $this->assertSame(
            77,
            (Cart::find($cmp->id))->{Cart::sopt()}
        );

        Config::set('shoppingcart.opt1', null);
        Cart::instance()->update($item->id, 25, ['asd']);
        $this->assertSame(
            ['asd'],
            (Cart::find($item->id))->options
        );
        $this->assertSame(
            25,
            (Cart::find($item->id))->qty
        );

        Config::set('shoppingcart.opt1', 'size');
        Config::set('shoppingcart.opt2', null);
        Cart::instance('wish')->update($wish->id, 88, 150, ['asd']);
        $this->assertSame(
            88,
            (Cart::find($wish->id))->qty
        );
        $this->assertSame(
            150,
            (Cart::find($wish->id))->{Cart::fopt()}
        );
        $this->assertSame(
            ['asd'],
            (Cart::find($wish->id))->options
        );
    }

    public function testUserCanUpdateItem()
    {
        $this->signIn();
        $this->testGuestCanUpdateItem();
    }

    public function testUpdatingInvalidItemWillThrowExc()
    {
        $this->expectException(ItemNotFoundException::class);

        Cart::update(63, 5);
    }
}
