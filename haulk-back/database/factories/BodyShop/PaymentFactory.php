<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use App\Models\BodyShop\Orders\Payment;

$factory->define(
    Payment::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'amount' => 10,
            'payment_method' => Payment::PAYMENT_METHOD_PAYPAL,
            'payment_date' => now(),
            'notes' => $faker->text,
        ];
    }
);
