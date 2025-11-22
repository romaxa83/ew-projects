<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireRelationshipTypeTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireRelationshipType|TireRelationshipType[]|Collection create(array $attributes = [])
 */
class TireRelationshipTypeFactory extends Factory
{
    protected $model = TireRelationshipType::class;

    public function definition(): array
    {
        return [
            'active' => true,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (TireRelationshipType $tireRelationshipType) {
                foreach (languages() as $language) {
                    TireRelationshipTypeTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $tireRelationshipType->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
