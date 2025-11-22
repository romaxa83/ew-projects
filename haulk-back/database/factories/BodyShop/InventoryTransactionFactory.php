<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\BodyShop\Inventories\Transaction;

$factory->define(
    Transaction::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'operation_type' => Transaction::OPERATION_TYPE_PURCHASE,
            'quantity' => $faker->randomFloat(2, 1, 1000),
            'price' => $faker->randomFloat(2, 1, 1000),
            'invoice_number' => $faker->text(15),
            'transaction_date' => now(),
            'is_reserve' => false,
        ];
    }
);
