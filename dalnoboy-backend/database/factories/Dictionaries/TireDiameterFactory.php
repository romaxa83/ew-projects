<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireDiameter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireDiameter|TireDiameter[]|Collection create(array $attributes = [])
 */
class TireDiameterFactory extends Factory
{
    protected $model = TireDiameter::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'value' => $this->faker->numerify,
        ];
    }
}
