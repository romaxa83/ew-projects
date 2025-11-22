<?php

namespace App\Foundations\Modules\Location\Factories;

use App\Foundations\Modules\Location\Models\State;
use Database\Factories\BaseFactory;
use Illuminate\Support\Str;

class StateFactory extends BaseFactory
{
    protected $model = State::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'state_short_name' => Str::upper($this->faker->unique()->lexify("??")),
            'country_code' => 'US',
            'country_name' => 'United States',
            'active' => true,
        ];
    }
}
