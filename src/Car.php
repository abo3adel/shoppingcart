<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Traits\Buyable;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use Buyable;
}
