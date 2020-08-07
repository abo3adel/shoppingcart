<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

use Abo3adel\ShoppingCart\Cart;
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
