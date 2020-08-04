<?php

namespace Abo3adel\ShoppingCart;

use Illuminate\Database\Eloquent\Model;

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

        $this->table = Cart::tbAddon() . 'cart_items';

        $this->casts += [
            Cart::fopt() => Cart::opt1Casts(),
            Cart::sopt() => Cart::opt2Casts(),
        ];
    }
}
