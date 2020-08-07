<?php

namespace Abo3adel\ShoppingCart\Events;


use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartInstanceDestroyed
{
    use Dispatchable, SerializesModels;

    public $instance;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(?string $instance)
    {
        $this->instance = $instance;
    }
}
