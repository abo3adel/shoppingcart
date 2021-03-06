<?php

namespace Abo3adel\ShoppingCart\Events;

use Abo3adel\ShoppingCart\CartItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartItemAdded
{
    use Dispatchable, SerializesModels;

    public $item;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CartItem $item)
    {
        $this->item = $item;
    }
}
