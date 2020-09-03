<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Transaction;
use Faker\Generator as Faker;

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'type' => $faker->randomElement([Transaction::TYPE_DEBIT, Transaction::TYPE_CREDIT]),
        'amount' => $faker->numberBetween(10, 100)
    ];
});
