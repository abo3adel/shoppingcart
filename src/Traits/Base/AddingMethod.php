<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Illuminate\Database\Eloquent\Model;

trait AddingMethod
{
    public function add(
        Model $buyable,
        $qty,
        $opt1 = null,
        $opt2 = null,
        $options = []
    ): CartItem {
        $item = new CartItem([
            'buyable_type' => get_class($buyable),
            'buyable_id' => $buyable->id,
            'options' => $options,
            'instance' => $this->instance,
            'price' => $buyable->price,
            'qty' => $qty,
        ]);

        $opt1Name = Cart::fopt();
        $opt2Name = Cart::sopt();

        if ($opt1Name) {
            $item->{$opt1Name} = $opt1;

            // check if opt2 is not null
            if ($opt2Name) {
                $item->{$opt2Name} = $opt2;
                $item->options = $options;
            } else {
                // then set the 4th argument to options
                $item->options = $opt2;
            }
        } else {
            // then set the 3rd argument to options
            $item->options = $opt1;
        }

        // check if user is logged in THEN save into database
        if (auth()->check()) {
            return CartItem::create([
                'user_id' => auth()->id(),
            ] + $item->toArray());
        }

        // generate random id for item in sessio
        $item->id = $this->generateId();

        collect(session(Cart::sessionName()))->push($item);

        return $item;
    }

    /**
     * generate a random and unique integer id
     *
     * @return integer
     */
    private function generateId(): int
    {
        $id = random_int(1, 999999999);

        // TODO check for existence with another method
        if (count(collect(session(Cart::sessionName()))->where('id', $id))) {
            $id = $this->generateId();
        }

        return $id;
    }
}
