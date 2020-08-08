<?php

namespace Abo3adel\ShoppingCart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'float',
        'qty' => 'int',
        'id' => 'int',
        'buyable_id' => 'int',
        'options' => 'array',
    ];

    protected $with = ['buyable'];

    protected $appends = [
        'sub_total',
    ];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->table = 'cart_items' . Cart::tbAddon();

        if (Cart::fopt()) {
            $this->casts += [
                Cart::fopt() => Cart::opt1Casts(),
            ];
        }
        if (Cart::sopt()) {
            $this->casts += [
                Cart::sopt() => Cart::opt2Casts(),
            ];
        }
    }

    public function getSubTotalAttribute(): float
    {
        return round($this->price * $this->qty, 2);
    }

    public function sub_total(
        int $decimals = 2,
        string $dec_point = '.',
        string $thousands_sep = ','
    ): string {
        return \number_format($this->sub_total, $decimals, $dec_point, $thousands_sep);
    }

    /**
     * Get the owning buyable model
     *
     * @return MorphTo
     */
    public function buyable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * increment qty
     *
     * @param integer $by
     * @return self|null
     */
    public function increments(int $by = 1): ?self
    {
        return Cart::instance()->increments($this, $by);
    }

    /**
     * decrement qty
     *
     * @param integer $by
     * @return self|null
     */
    public function decrements(int $by = 1): ?self
    {
        return Cart::instance()->decrements($this, $by);
    }
}
