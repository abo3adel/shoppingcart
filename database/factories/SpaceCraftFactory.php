<?php

use Abo3adel\ShoppingCart\Tests\Model\SpaceCraft;
use Faker\Generator as Faker;
use Illuminate\Foundation\Auth\User;

$factory->define(SpaceCraft::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class)->create(),
        'title' => $faker->sentence,
        'price' => $faker->randomFloat(2, 100, 10000),
        'weight' => random_int(20, 80),
        'color' => 'black',
    ];
});
