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
            return round($this->dbSum('price * qty'), 2);
        }

        return $this->content()->sum(function ($item) {
            return round($item->price * $item->qty, 2);
        });
    }

    /**
     * sum cloums in database
     *
     * @param string $exp cols to sum
     * @return float
     */
    private function dbSum(string $exp = ''): float
    {
        return DB::table(Cart::tbName())
            ->selectRaw('SUM(' . $exp . ') AS total')
            ->where('user_id', auth()->id())
            ->where('instance', $this->instance)
            ->first()->total;
    }
}
