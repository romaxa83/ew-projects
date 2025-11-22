<?php

namespace Database\Factories\Companies;

use App\Models\Companies;
use App\Models\Locations\State;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Companies\Contact|Companies\Contact[]|Collection create(array $attributes = [])
 */
class ContactFactory extends Factory
{
    protected $model = Companies\Contact::class;

    public function definition(): array
    {
        $state = State::first();
        return [
            'company_id' => Companies\Company::factory(),
            'name' => $this->faker->sentence,
            'email' => new Email($this->faker->unique()->safeEmail),
            'phone' => new Phone($this->faker->unique()->phoneNumber),
            'country_id' => $state->country_id,
            'state_id' => $state->id,
            'city' => $this->faker->city,
            'address_line_1' => $this->faker->streetName,
            'address_line_2' => $this->faker->streetName,
            'po_box' => $this->faker->postcode,
            'zip' => $this->faker->postcode,
        ];
    }
}


