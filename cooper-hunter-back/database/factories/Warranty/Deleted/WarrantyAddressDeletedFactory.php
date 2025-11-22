<?php

namespace Database\Factories\Warranty\Deleted;

use App\Models\Locations\State;
use App\Models\Warranty\Deleted\WarrantyAddressDeleted;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|WarrantyAddressDeleted[]|WarrantyAddressDeleted create(array $attributes = [])
 */
class WarrantyAddressDeletedFactory extends Factory
{
    protected $model = WarrantyAddressDeleted::class;

    public function definition(): array
    {
        $state = State::first();
        return [
            'warranty_id' => WarrantyRegistrationDeleted::factory(),
            'country_id' => $state->country_id,
            'state_id' => $state->id,
            'city' => $this->faker->city,
            'street' => $this->faker->streetAddress,
            'zip' => $this->faker->postcode
        ];
    }
}
