<?php

/** @var Factory $factory */

use App\Models\BodyShop\Suppliers\SupplierContact;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    SupplierContact::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'name' => $faker->name,
            'phone' => $faker->phoneNumber,
            'email' => $faker->email,
            'position' => $faker->text(50),
        ];
    }
);
