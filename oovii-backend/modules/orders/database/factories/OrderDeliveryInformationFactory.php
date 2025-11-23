<?php

namespace WezomCms\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use WezomCms\Orders\Models\OrderDeliveryInformation;

class OrderDeliveryInformationFactory extends Factory
{
    protected $model = OrderDeliveryInformation::class;

    private array $regions = [
        299 => [
            4756,
            11490,
            33806,
        ],
        294 => [
            4693,
        ],
        900 => [
            11789,
            4951,
        ],
        402 => [
            15481,
            11903,
            34384,
        ],
        500 => [
            7669,
            7144,
            5125,
        ],
    ];

    public function definition(): array
    {
        $region = array_rand($this->regions);
        $city = Arr::random($this->regions[$region]);

        return [
            'region_code' => $region,
            'city_code' => $city,
            'postal_code' => $this->faker->postcode,
            'tariff_code' => Arr::random(config('cms.orders.delivery-and-payment.sdek.tariffs', [])),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'street' => $this->faker->streetName,
            'house' => $this->faker->buildingNumber,
            'room' => $this->faker->numberBetween(1, 100),
        ];
    }
}
