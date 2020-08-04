<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Traits\Base\GetConfigKeysTrait;
use Abo3adel\ShoppingCart\Traits\Base\InstanceTrait;

class ShoppingCartCtrl
{

    use InstanceTrait, GetConfigKeysTrait;

    public function __construct()
    {
        $this->instance = $this->instance ?? $this->config('defaultInstance');

        if (!session()->has($this->sessionName())) {
            session()->put($this->sessionName(), []);
        }
    }
}