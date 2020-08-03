<?php

namespace Abo3adel\ShoppingCart;

use Illuminate\Support\Facades\Facade;

class ShoppingCartFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shopping-cart';
    }
}