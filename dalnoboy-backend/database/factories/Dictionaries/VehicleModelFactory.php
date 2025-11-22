<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\VehicleMake;
use App\Models\Dictionaries\VehicleModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method VehicleModel|VehicleModel[]|Collection create(array $attributes = [])
 */
class VehicleModelFactory extends Factory
{
    protected $model = VehicleModel::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'is_moderated' => true,
            'vehicle_make_id' => VehicleMake::factory(),
            'title' => $this->faker->name,
        ];
    }
}
