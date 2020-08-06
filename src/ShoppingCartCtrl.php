<?php

namespace Abo3adel\ShoppingCart;

use Abo3adel\ShoppingCart\Events\CartItemRemoved;
use Abo3adel\ShoppingCart\Traits\Base\AddingMethod;
use Abo3adel\ShoppingCart\Traits\Base\GetConfigKeysTrait;
use Abo3adel\ShoppingCart\Traits\Base\Helpers;
use Abo3adel\ShoppingCart\Traits\Base\InstanceTrait;
use Abo3adel\ShoppingCart\Traits\Base\UpdatingItemsMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShoppingCartCtrl
{

    use InstanceTrait,
        GetConfigKeysTrait,
        AddingMethod,
        UpdatingItemsMethod,
        Helpers;

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

        $cartItem = is_array($item) ? new CartItem($item) : $item;

        return  $item['buyable_type'] ? $cartItem : null;
    }

    /**
     * check if item exists in cart
     * 
     * @param integer $itemId
     * @param string|null $buyableType
     * @return boolean
     */
    public function has(
        int $itemId,
        ?string $buyableType = null
    ): bool {
        if (auth()->check()) {
            return !!($this->find($itemId, $buyableType));
        }

        return collect(session(Cart::sessionName()))
            ->whereStrict('instance', $this->instance)
            ->contains(function ($val) use (
                $itemId,
                $buyableType
            ) {
                if (null !== $buyableType) {
                    return $val['buyable_id'] === $itemId &&
                        $val['buyable_type'] === $buyableType;
                }

                return $val['id'] === $itemId;
            });
    }

    /**
     * retrive cart content
     *
     * @return Collection
     */
    public function content(): Collection
    {
        if (auth()->check()) {
            return CartItem::whereInstance($this->instance)
                ->whereUserId(auth()->id())
                ->get();
        }

        return collect(session(Cart::sessionName()))
            ->whereStrict('instance', $this->instance)
            ->map(function ($item) {
                return is_array($item) ? new CartItem($item) : $item;
            });
    }

    /**
     * remove item from cart
     *
     * @param integer $itemId
     * @return boolean
     */
    public function delete(int $itemId): bool
    {
        if (auth()->check()) {
            $item = $this->find($itemId);

            if ($item->delete()) {
                event(new CartItemRemoved($item));
                return true;
            }

            return false;
        }

        $items = collect(session(Cart::sessionName()))
            ->filter(function ($item) use ($itemId) {
                $item = (object) $item;

                if ($item->instance === $this->instance) {
                    if ($item->id === $itemId) {
                        event(new CartItemRemoved(
                            new CartItem((array) $item)
                        ));
                    }
                    return $item->id !== $itemId;
                }
                return $item;
            });

        return !!(session([Cart::sessionName() => $items]));
    }

    /**
     * delete all items with current instance
     *
     * @return boolean
     */
    public function destroy(): bool
    {
        if (auth()->check()) {
            return CartItem::whereInstance($this->instance)
                ->whereUserId(auth()->id())
                ->delete();
        }

        $items = collect(session(Cart::sessionName()))
            ->filter(function ($item) {
                $item = (object) $item;
                return $item->instance !== $this->instance;
            });

        return !!(session([Cart::sessionName() => $items]));
    }
}
