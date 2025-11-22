<?php

namespace Database\Factories\Locations;

use App\Models\Locations\State;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'status' => true,
            'state_short_name' => Str::upper($this->faker->unique()->lexify("??")),
            'country_code' => 'US',
            'country_name' => 'United States',
        ];
    }
}
