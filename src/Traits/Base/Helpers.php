<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;

trait Helpers
{
    /**
     * calculate cart item total (price & qty)
     *
     * @param boolean $formated
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep 
     * @return float|string
     */
    public function total(
        bool $formated = false,
        int $decimals = 2,
        string $dec_point = '.',
        string $thousands_sep = ','
    ) {
        if ($this->checkAuth()) {
            $sum = $this->dbSum('price * qty');

            if ($formated) {
                return $this->formatNumber(
                    $sum,
                    $decimals,
                    $dec_point,
                    $thousands_sep
                );
            }

            return $sum;
        }

        $sum = $this->content()->sum(function ($item) {
            return $item->price * $item->qty;
        });

        if ($formated) {
            return $this->formatNumber(
                $sum,
                $decimals,
                $dec_point,
                $thousands_sep
            );
        }

        return round($sum, 2);
    }

    /**
     * calculate total price only
     *
     * @return float
     */
    public function totalPrice(): float
    {
        if ($this->checkAuth()) {
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
        if ($this->checkAuth()) {
            return (int) $this->dbSum('qty');
        }

        return $this->content()->sum('qty');
    }

    /**
     * calculate sub total price after tax
     *
     * @param boolean $formated
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep 
     * @return float|string
     */
    public function subTotal(
        bool $formated = false,
        int $decimals = 2,
        string $dec_point = '.',
        string $thousands_sep = ','
    ) {
        $total = $this->total();
        $total -= ($total * ($this->tax / 100));

        if ($formated) {
            return $this->formatNumber(
                $total,
                $decimals,
                $dec_point,
                $thousands_sep
            );
        }

        return round($total, 2);
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
        if ($itemId instanceof CartItem) {
            $item = $itemId;
            $item->loadMissing('buyable');
            $item->buyable = (object) $item->buyable;
        } else {
            $item = $this->find($itemId);
        }

        // if the updated qty will be greater than buyable qty
        // then abort updating
        if (($item->qty + $by) > (int) $item->buyable->qty) {
            return null;
        }

        $item->qty += $by;
        unset($item->buyable);
        if ($this->checkAuth()) {
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
    public function decrements($itemId, int $by = 1): ?CartItem
    {
        $item = is_int($itemId) ? $this->find($itemId) : $itemId;

        // if the new qty will be less than one
        // then abort updating
        if (($item->qty - $by) < 1) {
            return null;
        }

        $item->qty -= $by;
        if ($this->checkAuth()) {
            $item->update();
        } else {
            $this->update($item->id, $item->qty);
        }

        return $item;
    }

    /**
     * set current user
     *
     * @param User $user
     * @return self
     */
    public function forUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * reset current user object
     *
     * @return void
     */
    public function resetUser(): void
    {
        $this->user = null;
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
                ->where('user_id', $this->user->id)
                ->where('instance', $this->instance)
                ->first()->total,
            2
        );
    }

    /**
     * format price
     * 
     * @param boolean $formated
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep 
     * @return string
     */
    private function formatNumber(
        $number,
        int $decimals = 2,
        string $dec_point = '.',
        string $thousands_sep = ','
    ): string {
        return \number_format($number, $decimals, $dec_point, $thousands_sep);
    }
}
