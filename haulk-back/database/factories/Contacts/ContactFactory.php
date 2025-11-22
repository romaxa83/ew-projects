<?php

namespace Database\Factories\Contacts;

use App\Models\Contacts\Contact;
use App\Models\Locations\City;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $city = City::factory()->create();
        return [
            'carrier_id' => 1,
            'user_id' => User::factory()->dispatcher(),
            'full_name' => $this->faker->firstName . ' ' . $this->faker->lastName,
            'address' => $this->faker->streetAddress,
            'city' => $city->name,
            'zip' => $city->zip,
            'phone_name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'fax' => $this->faker->e164PhoneNumber,
            'phone' => $this->faker->e164PhoneNumber,
            'state_id' => $city->state_id,
            'timezone' => $city->timezone,
            'type_id' => array_rand(Contact::CONTACT_TYPES)
        ];
    }

    public function name(string $name): self
    {
        return $this->state(
            [
                'full_name' => $name
            ]
        );
    }
}
