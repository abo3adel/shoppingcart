<?php

namespace Abo3adel\ShoppingCart\Traits\Base;

use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\CartItem;
use Abo3adel\ShoppingCart\Events\CartItemAdded;
use Abo3adel\ShoppingCart\Exceptions\InvalidModelException;
use Abo3adel\ShoppingCart\Exceptions\ItemAlreadyExistsException;
use Illuminate\Database\Eloquent\Model;

trait AddingMethod
{
    /**
     * add item to cart
     * 
     * @example add(Model, qty)
     * @example add(Model, qty, opt1, opt2, options)
     * @example add(Model, qty, options)
     * 
     *
     * @param Model $buyable
     * @param mixed|float $qty
     * @param mixed|null $opt1
     * @param mixed|null $opt2
     * @param array $options
     * @return CartItem
     */
    public function add(
        Model $buyable,
        $qty,
        $opt1 = null,
        $opt2 = null,
        $options = []
    ): CartItem {
        // check if buyable model have required attributes
        // price, id
        if (!method_exists($buyable, 'getSubPrice') || null === $buyable->getSubPrice() || null === $buyable->id) {
            throw new InvalidModelException(
                'model missing required attributes|methods ( getSubPrice() - id )'
            );
        }

        $item = new CartItem([
            'buyable_type' => get_class($buyable),
            'buyable_id' => $buyable->id,
            'options' => $options,
            'instance' => $this->instance,
            'price' => $buyable->getSubPrice(),
            'qty' => $qty,
        ]);

        // throw exc if item was already added before
        if ($this->has($buyable->id, get_class($buyable))) {
            throw new ItemAlreadyExistsException();
        }

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
        if ($this->checkAuth()) {
            // remove run time appends
            $itemArray = $item->toArray();
            unset($itemArray['sub_total']);
            unset($itemArray['discount']);

            $item = CartItem::create([
                'user_id' => $this->user->id,
            ] + $itemArray);

            event(new CartItemAdded($item));

            return $item;
        }

        // generate random id for item in sessio
        $item->id = $this->generateId();

        session([
            Cart::sessionName() => (collect(session(Cart::sessionName()))->push($item))->toArray()
        ]);

        event(new CartItemAdded($item));

        $item->loadMissing('buyable');

        return $item;
    }

    /**
     * generate a random and unique integer id
     *
     * @return integer
     */
    private function generateId(): int
    {
        $id = random_int(1, 9999999999);

        if ($this->has($id)) {
            $id = $this->generateId();
        }

        return $id;
    }
}
