<?php

namespace Database\Factories\Companies;

use App\Models\Companies;
use App\Models\Locations\State;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Companies\ShippingAddress|Companies\ShippingAddress[]|Collection create(array $attributes = [])
 */
class ShippingAddressFactory extends Factory
{
    protected $model = Companies\ShippingAddress::class;

    public function definition(): array
    {
        $state = State::first();
        return [
            'name' => $this->faker->company,
            'active' => true,
            'company_id' => Companies\Company::factory(),
            'phone' => new Phone($this->faker->unique()->phoneNumber),
            'fax' => new Phone($this->faker->unique()->phoneNumber),
            'country_id' => $state->country_id,
            'state_id' => $state->id,
            'city' => $this->faker->city,
            'address_line_1' => $this->faker->streetName,
            'address_line_2' => $this->faker->streetName,
            'zip' => $this->faker->postcode,
            'email' => new Email($this->faker->unique()->safeEmail),
            'receiving_persona' => $this->faker->userName(),
        ];
    }
}



