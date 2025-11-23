<?php

namespace Database\Factories\Divisions;

use App\Models\Division;
use Database\Factories\BaseFactory;

class DivisionFactory extends BaseFactory
{
    protected $model = Division::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'short' => $this->faker->countryCode(),
            'title' => $this->faker->streetName(),
            'miscs' => [
                'tz' => "America/Chicago",
                'phone' => '+1 (773) 236-87-97',
                'zadarma_pbx_id' => '373685',
                'zadarma_api_key' => null,
                'zadarma_api_secret' => null,
                'local_rates_season' => null,
                'local_rates_summer_from' => '03-31',
                'local_rates_summer_to' => '09-01',
                'domain' => "h2hmovers.com",
                'zadarma_pbx_caller_id' => "+17732368797",
                'ringostat_project_id' => "110070",
            ],
            'deleted_at' => null,

        ];
    }
}


