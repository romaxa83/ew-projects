<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Str;

class OrderSignatureFactory extends Factory
{
    protected $model = OrderSignature::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->email,
            'inspection_location' => Arr::random([Order::LOCATION_DELIVERY, Order::LOCATION_PICKUP]),
            'signature_token' => hash('sha256', Str::random(60))
        ];
    }
}
