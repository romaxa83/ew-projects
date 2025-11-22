<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method VehicleMake|VehicleMake[]|Collection create(array $attributes = [])
 */
class VehicleMakeFactory extends Factory
{
    protected $model = VehicleMake::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'is_moderated' => true,
            'title' => $this->faker->unique->word,
        ];
    }

    public function addModels(): self
    {
        return $this->afterCreating(
            static fn (VehicleMake $vehicleMake) => VehicleModel::factory(['vehicle_make_id' => $vehicleMake->id])
                ->count(2)
                ->create()
        );
    }
}
