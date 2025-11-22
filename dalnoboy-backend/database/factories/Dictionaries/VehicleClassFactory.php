<?php

namespace Database\Factories\Dictionaries;

use App\Enums\Vehicles\VehicleFormEnum;
use App\Models\Dictionaries\VehicleClass;
use App\Models\Dictionaries\VehicleClassTranslate;
use App\Models\Dictionaries\VehicleType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method VehicleClass|VehicleClass[]|Collection create(array $attributes = [])
 */
class VehicleClassFactory extends Factory
{
    protected $model = VehicleClass::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'vehicle_form' => VehicleFormEnum::MAIN,
        ];
    }

    public function trailer(): self
    {
        return $this->state([
            'vehicle_form' => VehicleFormEnum::TRAILER
        ]);
    }

    public function addTypes(): self
    {
        return $this->afterCreating(
            static fn (VehicleClass $vehicleClass) => $vehicleClass
                ->vehicleTypes()
                ->sync(VehicleType::factory()->count(2)->create())
        );
    }

    public function configure(): self
    {
        return $this->afterCreating(
            static function (VehicleClass $vehicleClass) {
                foreach (languages() as $language) {
                    VehicleClassTranslate::factory()->create(
                        [
                            'title' => 'test title',
                            'row_id' => $vehicleClass->id,
                            'language' => $language->slug
                        ]
                    );
                }
            }
        );
    }
}
