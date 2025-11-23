<?php

namespace WezomCms\Providers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;

class ProviderFactory extends Factory
{
    protected $model = Provider::class;

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
            'status' => ProviderStatus::DRAFT,
            'active' => $this->faker->boolean,
            'name' => $this->faker->lastName() . $this->faker->firstName(),
            'phone' => $this->faker->unique()->numerify('+380#########'),
            'phone_verified' => $this->faker->boolean,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified' => $this->faker->boolean,
            'password' => 'password',
            'company' => $this->faker->company,
            'region_code' => $region,
            'city_code' => $city,
            'address' => $this->faker->streetAddress,
        ];
    }
}
