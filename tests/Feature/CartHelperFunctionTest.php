<?php

namespace Abo3adel\ShoppingCart\Tests\Feature;

use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Abo3adel\ShoppingCart\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartHelperFunctionTest extends TestCase
{
    use RefreshDatabase;

    public function testItCanAddItems()
    {
        $buyable = factory(SpaceCraft::class)->create();

        $item = cart()->instance()->add($buyable, 25);

        $this->assertSame($item->buyable_id, $buyable->id);
        $this->assertSame(
            25,
            (cart()->find($item->id))->qty

        );

        $this->assertSame('default', cart()->getInstance());
    }
}
