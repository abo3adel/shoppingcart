<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Traits\Base\AddingMethod;
use Abo3adel\ShoppingCart\Traits\Base\GetConfigKeysTrait;
use Abo3adel\ShoppingCart\Traits\Base\InstanceTrait;
use Illuminate\Database\Eloquent\Model;

class ShoppingCartCtrl
{

    use InstanceTrait, GetConfigKeysTrait, AddingMethod;

    public function __construct()
    {
        $this->instance = $this->instance ?? $this->config('defaultInstance');

        if (!session()->has($this->sessionName())) {
            session()->put($this->sessionName(), []);
        }
    }

    public function find(
        int $itemId,
        ?string $buyableType = null
    ): ?CartItem {
        if (auth()->check()) {
        }

        $item = collect(session(Cart::sessionName()))
            ->whereStrict('instance', $this->instance);

        if (null !== $buyableType) {
            $item = $item->whereStrict('buyable_id', $itemId)
                ->whereStrict('buyable_type', $buyableType)
                ->first();
        } else {
            $item = $item->whereStrict('id', $itemId)
                ->first();
        }

        return  $item['buyable_type'] ? new CartItem($item) : null;
    }
}
