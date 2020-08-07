<?php

namespace Abo3adel\ShoppingCart\Traits;

use Abo3adel\ShoppingCart\Cart;
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

    /**
     * add this model to cart
     *
     * @param mixed|float $qty
     * @param mixed|null $opt1
     * @param mixed|null $opt2
     * @param array $options
     * @return CartItem
     */
    public function addToCart(
        $qty,
        $opt1 = null,
        $opt2 = null,
        $options = []
    ): CartItem
    {
        return Cart::add($this, $qty, $opt1, $opt2, $options);
    }
}