<?php

namespace WezomCms\Orders\Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use WezomCms\Orders\Models\UserAddress;
use WezomCms\Users\Models\User;

class UserAddressFactory extends Factory
{
    protected $model = UserAddress::class;

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
        /** @var User $user */
        $user = User::factory()->create();

        $region = array_rand($this->regions);
        $city = Arr::random($this->regions[$region]);

        return [
            'user_id' => $user->id,
            'region_code' => $region,
            'region' => $this->faker->state,
            'city_code' => $city,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'name' => $this->faker->word,
            'address' => $this->faker->streetAddress,
        ];
    }
}
