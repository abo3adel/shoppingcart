<?php

use Faker\Generator as Faker;
use Illuminate\Foundation\Auth\User;

$factory->define(Abo3adel\ShoppingCart\SpaceCraft::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create(),
        'title' => $faker->sentence,
        'price' => $faker->randomFloat(2, 100, 10000),
        'weight' => random_int(20, 80),
        'color' => 'black',
    ];
});
