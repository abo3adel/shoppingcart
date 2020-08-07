<?php

namespace Abo3adel\ShoppingCart\Tests\Model;

use Abo3adel\ShoppingCart\Contracts\CanBeBought;
use Abo3adel\ShoppingCart\Traits\Buyable;
use Illuminate\Database\Eloquent\Model;

class Car extends Model implements CanBeBought
{
    use Buyable;

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDiscount(): float
    {
        return 0;
    }
}
