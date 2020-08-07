<?php

namespace Abo3adel\ShoppingCart\Listeners;

use Abo3adel\ShoppingCart\Cart;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SaveCartItemsIntoDataBase
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        Cart::afterLogin($event->user);
    }
}
