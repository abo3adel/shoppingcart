<?php

use Faker\Generator as Faker;
use Illuminate\Foundation\Auth\User;

$factory->define(Abo3adel\ShoppingCart\Car::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create(),
        'title' => $faker->sentence,
        'price' => $faker->randomFloat(2, 100, 10000),
        'qty' => random_int(20, 80),
        'sizes' => json_encode(['XS', 'S', 'M', 'L', 'XL']),
    ];
});
