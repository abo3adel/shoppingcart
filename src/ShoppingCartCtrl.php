<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Traits\Base\InstanceTrait;

class ShoppingCartCtrl
{

    use InstanceTrait;

    public function __construct()
    {
        $this->instance = $this->instance ?? $this->config('defaultInstance');

        if (!session()->has($this->config('session_name'))) {
            session()->put($this->config('session_name'), []);
        }
    }

    private function config(string $key): ?string
    {
        return config('shoppingcart.'. $key, null);
    }
}