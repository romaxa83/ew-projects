<?php

namespace Database\Factories\Warranty\WarrantyInfo;

use App\Models\Locations\State;
use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|WarrantyAddress[]|WarrantyAddress create(array $attributes = [])
 */
class WarrantyAddressFactory extends Factory
{
    protected $model = WarrantyAddress::class;

    public function definition(): array
    {
        $state = State::first();

        return [
            'warranty_id' => WarrantyRegistration::factory(),
            'country_id' => $state->country_id,
            'state_id' => $state->id,
            'city' => $this->faker->city,
            'street' => $this->faker->streetAddress,
            'zip' => $this->faker->postcode
        ];
    }
}
