<?php

namespace Database\Factories\Tires;

use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Tires\Tire;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Tire|Tire[]|Collection create(array $attributes = [])
 */
class TireFactory extends Factory
{
    protected $model = Tire::class;

    public function definition(): array
    {
        $specification = TireSpecification::factory()
            ->create();
        return [
            'active' => true,
            'is_moderated' => true,
            'specification_id' => $specification->id,
            'relationship_type_id' => TireRelationshipType::factory(),
            'serial_number' => $this->faker->jobTitle,
            'ogp' => $specification->ngp
        ];
    }
}
