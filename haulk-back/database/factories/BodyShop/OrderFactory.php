<?php

/** @var Factory $factory */

use App\Models\BodyShop\Orders\Order;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(
    Order::class,
    function (Faker $faker, ?array $data = null) {
        return [
            'order_number' => date('Ymd-' . $faker->numberBetween(1, 100)),
            'truck_id' => (!isset($data['truck_id']) && !isset($data['trailer_id'])) ? (factory(Truck::class)->create(
            ))->id : $data['truck_id'] ?? null,
            'discount' => $faker->randomFloat(2, 0, 99),
            'tax_labor' => $faker->randomFloat(2, 0, 99),
            'tax_inventory' => $faker->randomFloat(2, 0, 99),
            'implementation_date' => now()->format('Y-m-d H:i'),
            'mechanic_id' => $data['mechanic_id'] ?? User::factory()->create()->id,
            'status' => Order::STATUS_NEW,
            'due_date' => now()->format('Y-m-d H:i'),
        ];
    }
);
