<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireType;
use App\Models\Dictionaries\TireTypeTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireType|TireType[]|Collection create(array $attributes = [])
 */
class TireTypeFactory extends Factory
{
    protected $model = TireType::class;

    public function definition(): array
    {
        return [
            'active' => true,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (TireType $tireType) {
                foreach (languages() as $language) {
                    TireTypeTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $tireType->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
