<?php

namespace Abo3adel\ShoppingCart\Listeners;

use Abo3adel\ShoppingCart\Cart;

class SaveCartItemsIntoDataBase
{

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Cart::resetUser();
        Cart::afterLogin($event->user);
    }
}
