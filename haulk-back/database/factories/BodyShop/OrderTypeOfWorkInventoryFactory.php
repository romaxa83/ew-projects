<?php

/** @var Factory $factory */

use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    TypeOfWorkInventory::class,
    function (Faker $faker, ?array $data = null) {
        return [];
    }
);
