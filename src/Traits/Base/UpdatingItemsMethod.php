<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;

trait UpdatingItemsMethod
{
    public function update(
        int $itemId,
        $qty,
        $opt1 = null,
        $opt2 = null,
        array $options = []
    ): ?CartItem {
        // we will attach our updated item to this variable
        $cartItem = null;

        if (auth()->check()) {
        }

        $items = (collect(session(Cart::sessionName())))
            ->map(function (
                $item
            ) use (
                $itemId,
                $qty,
                $opt1,
                $opt2,
                $options,
                $cartItem
            ) {
                $item = is_array($item) ? new CartItem($item) : $item;
                if ($item->id === $itemId) {
                    if (is_int($qty)) {
                        $item->qty = $qty;
                    } elseif (is_array($qty)) {
                        $item->options = $qty;
                    }

                    if (sizeof($options)) {
                        $item->options = $options;
                    }

                    if (null !== $opt1) {
                        if (Cart::fopt()) {
                            $item->{Cart::fopt()} = $opt1;

                            if (Cart::sopt()) {
                                $item->{Cart::sopt()} = $opt2;
                            } else {
                                $item->options = $opt2;
                            }
                        } else {
                            $item->options = $opt1;
                        }
                    }
                    // dd($item->toArray());
                    $cartItem = $item;
                }

                return $item;
            });

        session([Cart::sessionName() => $items]);

        return $cartItem;
    }
}
