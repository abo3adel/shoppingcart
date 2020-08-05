<?php

namespace Abo3adel\ShoppingCart\Traits;

use Abo3adel\ShoppingCart\CartItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Buyable
{
    public function items(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'buyable');
    }
}