<?php
/** @var Factory $factory */

use App\Models\Billing\Invoice;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Invoice::class,
    function (Faker $faker) {
        return [
            'carrier_id' => $faker->randomNumber(),
            'created_at' => now(),
            'updated_at' => now(),
            'billing_start' => $faker->date('Y-m-d', now()->subMonth()),
            'billing_end' => $faker->date(),
            'amount' => $faker->randomFloat(2, 0, pow(10, 4)),
            'pending' => false,
            'trans_id' => null,
            'is_paid' => $faker->boolean,
            'public_token' => $faker->md5,
            'company_name' => $faker->company
        ];
    }
);

