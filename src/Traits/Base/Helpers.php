<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Illuminate\Support\Facades\DB;

trait Helpers
{
    /**
     * calculate cart item total (price & qty)
     *
     * @return float
     */
    public function total(): float
    {
        if (auth()->check()) {
            return $this->dbSum('price * qty');
        }

        return $this->content()->sum(function ($item) {
            return round($item->price * $item->qty, 2);
        });
    }

    /**
     * calculate total price only
     *
     * @return float
     */
    public function totalPrice(): float
    {
        if (auth()->check()) {
            return $this->dbSum('price');
        }

        return round($this->content()->sum('price'), 2);
    }

    /**
     * calculate total qty only
     *
     * @return integer
     */
    public function totalQty(): int
    {
        if (auth()->check()) {
            return (int) $this->dbSum('qty');
        }

        return $this->content()->sum('qty');
    }

    /**
     * calculate sub total price after tax
     *
     * @return float
     */
    public function subTotal(): float
    {
        $total = $this->total();

        return $total - ($total * ($this->tax / 100));
    }

    /**
     * set cart tax for this instance only
     *
     * @param integer|null $val
     * @return int|self
     */
    public function setTax(?int $val = 0): self
    {
        $this->tax = $val;

        return $this;
    }

    /**
     * get cart tax
     *
     * @return integer
     */
    public function getTax(): int
    {
        return $this->tax;
    }

    /**
     * increment item qty by provided number
     *
     * @param integer|CartItem $itemId
     * @param integer $by
     * @return CartItem
     */
    public function increments($itemId, int $by = 1): ?CartItem
    {
        $item = ($itemId instanceof CartItem) ?
            $itemId :
            $this->find($itemId);

        // if $by is greater than buyable qty
        // then abort updating
        if ($by > (int) $item->buyable->qty) {
            return null;
        }

        $item->qty += $by;
        if (auth()->check()) {
            $item->update();
        } else {
            $this->update($item->id, $item->qty);
        }

        return $item;
    }

    /**
     * decrement item qty by provieded number
     *
     * @param integer|CartItem $itemId
     * @param integer $by
     * @return CartItem
     */
    public function decrement($itemId, int $by = 1): ?CartItem
    {
        $item = is_int($itemId) ? $this->find($itemId) : $itemId;

        // if $by is greater than item qty
        // then abort updating
        if ($by > $item->qty) {
            return null;
        }

        $item->qty -= $by;
        if (auth()->check()) {
            $item->update();
        } else {
            $this->update($item->id, $item->qty);
        }

        return $item;
    }

    /**
     * sum cloumns in database
     *
     * @param string $exp columns to sum
     * @return float
     */
    private function dbSum(string $exp = ''): float
    {
        return round(
            DB::table(Cart::tbName())
                ->selectRaw('SUM(' . $exp . ') AS total')
                ->where('user_id', auth()->id())
                ->where('instance', $this->instance)
                ->first()->total,
            2
        );
    }
}
