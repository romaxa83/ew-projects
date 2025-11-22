<?php

namespace Database\Factories\Vehicles;

use App\Models\Vehicles\Make;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicles\Make>
 */
class MakeFactory extends BaseFactory
{
    protected $model = Make::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => $this->faker->title,
            'last_updated' => CarbonImmutable::now(),
        ];
    }
}
