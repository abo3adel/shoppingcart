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

    /**
     * retrive item from cart
     * 
     * @example find(5)
     * @example find(2, 'App\Product')
     *
     * @param integer $itemId
     * @param string|null $buyableType
     * @return CartItem|null
     */
    public function find(
        int $itemId,
        ?string $buyableType = null
    ): ?CartItem {
        if (auth()->check()) {
            if (null !== $buyableType) {
                return CartItem::whereInstance($this->instance)
                    ->whereUserId(auth()->id())
                    ->where('buyable_id', $itemId)
                    ->where('buyable_type', $buyableType)
                    ->first();
            }

            return CartItem::find($itemId);
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
