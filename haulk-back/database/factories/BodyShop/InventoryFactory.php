<?php

/** @var Factory $factory */

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Unit;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Inventory::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'name' => $faker->name,
            'stock_number' => $faker->text(20),
            'price_retail' => $faker->randomFloat(2, 1, 1000),
            'quantity' => $faker->randomNumber(),
            'unit_id' => (factory(Unit::class)->create())->id,
            'for_unit' => false,
        ];
    }
);
