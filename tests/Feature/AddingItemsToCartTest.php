<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class AddingItemsToCartTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testGuestCanAddItemWithMinimalArgs()
    {
        $buyable = factory(SpaceCraft::class)->create([
            'price' => 553
        ]);

        $item = Cart::add($buyable, 22);
        $this->assertSame((float)553, $item->price);
        $this->assertSame(22, $item->qty);
    }

    public function testGuestCanAddItemWithOpt1AndOpt2()
    {
        $buyable = factory(SpaceCraft::class)->create([
            'price' => 15056
        ]);

        $item = Cart::add($buyable, 38, 6);
        $this->assertSame((float)15056, $item->price);
        $this->assertSame(38, $item->qty);
        $this->assertSame(6, $item->{Cart::fopt()});
        $this->assertNull($item->{Cart::sopt()});

        $item = Cart::add($buyable, 70, 4, 13);
        $this->assertSame(70, $item->qty);
        $this->assertSame(4, $item->{Cart::fopt()});
        $this->assertSame(13, $item->{Cart::sopt()});
    }

    public function testGuestCanAddItemWithAllArgs()
    {
        $buyable = factory(SpaceCraft::class)->create([
            'price' => 167
        ]);

        $item = Cart::add($buyable, 66, 5, 2, [
            'name' => $this->faker->name
        ]);
        $this->assertSame((float)167, $item->price);
        $this->assertSame(66, $item->qty);
        $this->assertSame(5, $item->{Cart::fopt()});
        $this->assertSame(2, $item->{Cart::sopt()});
        $this->assertArrayHasKey('name', $item->options);
    }

    public function testGuestCanAddItemWithoutFirstOpt()
    {
        $buyable = factory(SpaceCraft::class)->create();

        Config::set('shoppingcart.opt1', null);
        $item = Cart::add($buyable, 99, [
            'name' => $this->faker->name
        ]);
        $this->assertSame(99, $item->qty);
        $this->assertArrayHasKey('name', $item->options);
    }

    public function testGuestCanAddItemWithoutSecondOpt()
    {
        $buyable = factory(SpaceCraft::class)->create();

        Config::set('shoppingcart.opt2', null);
        $item = Cart::add($buyable, 17, 96, [
            'name' => $this->faker->name
        ]);
        $this->assertSame(17, $item->qty);
        $this->assertSame(96, $item->{Cart::fopt()});
        $this->assertArrayHasKey('name', $item->options);
    }

    private function createItem(
        ?int $count = 1,
        ?array $attrs = []
    ) {
        return factory(CartItem::class, $count)->create($attrs); 
    }
}
