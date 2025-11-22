<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\VehicleType;
use App\Models\Dictionaries\VehicleTypeTranslate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method VehicleType|VehicleType[]|Collection create(array $attributes = [])
 */
class VehicleTypeFactory extends Factory
{
    protected $model = VehicleType::class;

    public function definition(): array
    {
        return [
            'active' => true,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (VehicleType $vehicleType) {
                foreach (languages() as $language) {
                    VehicleTypeTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $vehicleType->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
