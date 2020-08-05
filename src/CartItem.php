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
        'options' => 'array',
    ];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->table = 'cart_items' . Cart::tbAddon();

        $this->casts += [
            Cart::fopt() => Cart::opt1Casts(),
            Cart::sopt() => Cart::opt2Casts(),
        ];
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
}
