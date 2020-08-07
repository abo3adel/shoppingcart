<?php

namespace Abo3adel\ShoppingCart\Traits;

use Abo3adel\ShoppingCart\CartItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Buyable
{
    /**
     * get cart items
     *
     * @return MorphMany
     */
    public function items(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'buyable');
    }

    /**
     * get subTotal price or
     * price after discount
     *
     * @return float
     */
    public function getSubPrice(): float
    {
        return round($this->getPrice() - ($this->getDiscount() / 100 * $this->getPrice()), 2);
    }
}