<?php

use Abo3adel\ShoppingCart\ShoppingCartCtrl;

if (!function_exists('cart')) {
    function cart()
    {
        return app(ShoppingCartCtrl::class);
    }
}