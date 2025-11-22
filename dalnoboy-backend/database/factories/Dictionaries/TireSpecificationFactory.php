<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Dictionaries\TireType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireSpecification|TireSpecification[]|Collection create(array $attributes = [])
 */
class TireSpecificationFactory extends Factory
{
    protected $model = TireSpecification::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'is_moderated' => true,
            'make_id' => TireMake::factory(),
            'model_id' => TireModel::factory(),
            'type_id' => TireType::factory(),
            'size_id' => TireSize::factory(),
            'ngp' => $this->faker->numerify,
        ];
    }
}
