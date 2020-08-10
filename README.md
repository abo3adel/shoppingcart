## Shopping Cart

---

a simple yet powerful and highly customized laravel shopping cart.

Highly inspired by [LaravelShoppingcart](https://github.com/Crinsane/LaravelShoppingcart)

[![Latest Stable Version](https://poser.pugx.org/abo3adel/shoppingcart/v)](//packagist.org/packages/abo3adel/shoppingcart) [![Total Downloads](https://poser.pugx.org/abo3adel/shoppingcart/downloads)](//packagist.org/packages/abo3adel/shoppingcart) [![Latest Unstable Version](https://poser.pugx.org/abo3adel/shoppingcart/v/unstable)](//packagist.org/packages/abo3adel/shoppingcart) [![License](https://poser.pugx.org/abo3adel/shoppingcart/license)](//packagist.org/packages/abo3adel/shoppingcart)

#### Demo [shopping-cart](http://spa.aboadeltestblog.epizy.com/)

## Features

- Multiple cart instances
- Multiple Buyable models
- Discount && Tax
- configure up to 2 colums in cart item
- Guest cart items && user cart items
- It`s more like the Laravel way

## Requirements

- PHP 7.1+ (tested in 7.2+)
- Laravel 5.5

### How It works

**This package use sessions and database, it will save cart items into session for guests and then merge these items when user logIn and save it into database, while user is loggedIn items will be saved into database**

## Installation

1. require

```php
composer require abo3adel/shoppingcart
```

2. Import config file and migration and migrate

```php
php artisan vendor:publish --tag=shoppingcart-all
```

3. configure before migrate

```php
update config/shoppingcart.php with your configration
and then
php artisan migrate
```

4. Add a trait and interface (required) to the model that can be bought

```php
<?php

namespace Abo3adel\ShoppingCart\Tests\Model;

use Abo3adel\ShoppingCart\Contracts\CanBeBought;
use Abo3adel\ShoppingCart\Traits\Buyable;
use Illuminate\Database\Eloquent\Model;

class SpaceCraft extends Model implements CanBeBought
{
    use Buyable;

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    //
}
```

## Overview

- [Usage](#usage)
- [Instances](#instances)
- [Helpers](#helpers)
- [Models](#models)
- [Bonus](#bonus)
- [Exceptions](#exceptions)
- [Events](#events)
- [Commands](#commands)
- [Example](#example)
- [Tests](#tests)
- [Contribution](#contribution)
- [License](#license)

## Usage

you can use the facade or the helper function

```php
Cart::add()

cart()->add()
```

### Cart::add()

> return the newly saved item

```php
// all options
Cart::add(
    CanBeBought $buyable,
    int $qty,
    mixed $opt1, // see config/shoppingcart.php to change this
    mixed $opt2, // see config/shoppingcart.php to change this
    array $options
)

// only price and qty
Cart::add($buyable, $qty)

// only price && qty && options array
// use this if you do not use any of (opt1 or opt2)
Cart::add($buyable, $qty, ['weight' => 250])

// if you do not use opt2
Cart::add($buyable, $qty, $opt1, ['weight' => 250])
```

### Cart::find()

> return the cart item if found || else null

```php
// by item ID
Cart::find($itemId)

// by buyable model id
Cart::find(int $buyable_id, string $buyable_type)
Cart::find(25, App\Book::class)
```

### Cart::update()

> return boolean

```php
// all args
Cart::add(
    int $itemId,
    int $qty,
    mixed $opt1, // see config/shoppingcart.php to change this
    mixed $opt2, // see config/shoppingcart.php to change this
    array $options
)

// only qty
Cart::update($itemId, $qty)

// only options
Cart::update($itemId, $options)

// if no (opt1 || opt2)
Cart::update($itemId, $qty, $options)

// if no opt2
Cart::update($itemId, $qty, $opt1, $options)
```

### Cart::delete()

> return boolean

```php
Cart::delete(int $itemId)
```

### Cart::has()

> return boolean

```php
// by item id
Cart::has($itemId)

// by buyable id
Cart::has($buyable_id, $buyable_type)
Cart::has(5, 'App\Product')
```

### Cart::content()

> return collection of cart items for current instance.
> all collection methods allowed see [Collections](https://laravel.com/docs/7.x/collections)

```php
Cart::content()

Cart::content()->count() // get count

Cart::content()->search() // search

Cart::content()->each() // loop

// or any collection method
```

### Cart::destroy()

delete all items for current instance

> count of deleted items

```php
Cart::destroy()
```

## Instances

**Cart stays in the last set instance if you don't set a different one**

```php
Cart::instance()->content() // default instance

Cart::instance('wishlist')->content()
Cart::getInstance() // wishlist
Cart::destroy() // instance still wishlist

Cart::instance('compare')->add($buyable, 2)
Cart::getInstance() // compare

Cart::instance()->delete(5)
Cart::getInstance() // default
```

## Helpers

### Cart::total()

> get the sum of all items sub_total (qty \* price)

```php
Cart::total() // 5631.25

// if you want the total to be formated
Cart::total(true)
Cart::total($formated, $decimals, $dec_point, $thousands_sep)
```

### Cart::totalPrice()

> the sum of all items price

### Cart::totalQty()

> the sum of all items qty

### Cart::subTotal()

> get the total minus tax percentage

```php
Cart::subTotal() // 2516.32

Cart::subTotal(true) // 2,516.32
Cart::subTotal($formated, $decimals, $dec_point, $thousands_sep)
```

### Cart::setTax()

> set tax percentage for current instance only
> does not affect the configured value

```php
Cart::setTax(25)->subTotal()
```

### Cart::getTax()

> retrieve current tax percentage

### Cart::increments()

> increase item qty

```php
Cart::increments($itemId, $numberToAdd)
```

### Cart::decrements()

> decrease item qty

```php
Cart::decrements($itemId, $numberToAdd)
```

## Models

### CartItem

```php
$cartItem = Cart::add($buyable, 5)

// get subtotal (price * qty)
$cartItem->sub_total

// get formated subtotal
$cartItem->sub_total()
$cartItem->sub_total($decimals, $dec_point, $thousands_sep)

// increment item qty
$cartItem->increments($numberToAdd)

// decrement item qty
$cartItem->decrements($numberToSubstract)

// access the buyable object
$cartItem->buyable
```

### Buyable

```php
// add to cart in the default instance
$buyable->addToCart($qty, $opt1, $opt2, $options)

// remove from cart in the default instance
$buyable->removeFromCart()

// get list of all cart items associated with this model
$buyable->items()

// get the subTotal price after discount substract
$buyable->getSubPrice()
```

## Bonus

### Manage another user cart items

> Cart stays in the last set User if you don't set a different one

```php
// $admin is logged in
// user here is admin

Cart::forUser($user)
// user here is $user
Cart::add($buyable, $qty)

// reset user and return to logged in admin
Cart::resetUser()

// user here is $admin again
```

### Cart::checkBuyableStockAmount()

> this will delete cart items which buyable is out of stock
> and lower items qty if it exceeded buyable qty

```php
$buyable1->qty = 0 // out of stock (will be removed)

$buyable2->qty = 5
$cartItem->qty = 7 // this exceeded it`s buyable qty

[
    [$buyable1], // deleted items
    [
        'from' => 7,
        'to' => 2,
        'items' => [$buyable2] // updated items qty
    ]
] = Cart::checkBuyableStockAmount()
```

### Refresh Items Buyable Object

> only required in session
> this will update the buyable object with latest changes
> you can use this to check if item buyable still in stock

```php
// this initial values
$buyable->qty // 4
$item->buyable->qty // 4

// this after updating buyable qty
$buyable->qty // 10
$item->buyable->qty // 4 // still not updated

Cart::refreshItemsBuyableObjects()

$item->buyable->qty // 10 // updated
```

## Exceptions

| Exception                  | Reason                                                                  |
| -------------------------- | ----------------------------------------------------------------------- |
| InvalidModelException      | Provided buyable model is missing required attributes (getPrice() - id) |
| ItemAlreadyExistsException | trying to add new item that was already added                           |
| ItemNotFoundException      | trying to update or delete an item that is not found                    |

## Events

| Event                 | Fired                                       | Parameter    |
| --------------------- | ------------------------------------------- | ------------ |
| CartItemAdded         | When an item was added to the cart.         | the CartItem |
| CartItemUpdated       | When an item was updated                    | the CartItem |
| CartItemRemoved       | when an item was removed                    | the CartItem |
| CartInstanceDestroyed | when all items for this instance is deleted | instance     |

## Commands

> this will delete old cart item from database older than configured `deleteAfter` value

Schedule the RemoveOldItemsCommand

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('shoppingcart:destroy')->daily();
}
```

## Example

---

```php
$buyable = Product::find(1);

$item = $buyable->addToCart(10, 6, 32, ['height' => '340m']);
// or $item = Cart::add($buyable, 10, 6, 32, ['height' => '340m'])

$wishListItem = Cart::instance('wishlist')->add($buyable, 0);
$anotherWishListItem = Cart::instance('wishlist')->add($anotherBuyable, 0);

echo $item->size; // 6 , opt1 => size
echo $item->color; // 32 , opt2 => color

$item->increments(3);
echo $item->qty; // 13

// return to default instance and update item
Cart::instance()->update($item->id, ['height' => '260m']);

// find updated item
$item = Cart::find($item->id);
echo $item->options; // ['height' => '260m']

foreach (Cart::content() as $item) {
    echo $item->sub_total; // 2653.14    not formated
    echo $item_sub_total(); // 2,653.14  formated
}

// get all items subTotal
echo Cart::subTotal(); // 6532145.2
echo Cart::subTotal(true); // 6,532,145.20

echo Cart::instance('wishlist')->content()->count() // 2
```

## Tests

```php
composer test
```

## Contribution

Contributions are **welcome** and will be fully **credited**.
see [CONTRIBUTING.md](./CONTRIBUTING.md)

## License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details
