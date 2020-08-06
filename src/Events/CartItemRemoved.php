<?php

namespace Abo3adel\ShoppingCart\Events;

use Abo3adel\ShoppingCart\CartItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartItemRemoved
{
    use Dispatchable, SerializesModels;

    public $item;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CartItem $cartItem)
    {
        $this->item = $cartItem;
    }
}
