<?php

namespace Database\Factories\Partners;

use App\Models\Division;
use App\Models\Partners\Partner;
use Database\Factories\BaseFactory;

class PartnerFactory extends BaseFactory
{
    protected $model = Partner::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'division_id' => Division::factory(),
            'contact_person' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
        ];
    }
}
