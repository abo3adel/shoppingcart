<?php

use Abo3adel\ShoppingCart\Tests\Model\Car;
use Abo3adel\ShoppingCart\Cart;
use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Faker\Generator as Faker;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Arr;

$factory->define(Abo3adel\ShoppingCart\CartItem::class, function (Faker $faker) {
    $buyableArr = [
        Car::class,
        SpaceCraft::class,
    ];
    $buyableType = Arr::random($buyableArr);
    $buyable = factory($buyableType)->create(); 

    $tb = [
        'user_id' => factory(User::class)->create(),
        'buyable_type' => $buyableType,
        'buyable_id' => $buyable->id,
        'price' => $buyable->price,
        'qty' => $buyable->qty ?? random_int(10, 90),
        'options' => [],
    ];

    if (Cart::fopt()) {
        $tb += [Cart::fopt() => random_int(1, 10)];
    }

    if (Cart::sopt()) {
        $tb += [Cart::sopt() => random_int(1, 10)];
    }

    return $tb;
});
