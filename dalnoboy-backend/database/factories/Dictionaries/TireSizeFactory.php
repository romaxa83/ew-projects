<?php

namespace Database\Factories\Dictionaries;

use App\Models\Dictionaries\TireDiameter;
use App\Models\Dictionaries\TireHeight;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireWidth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method TireSize|TireSize[]|Collection create(array $attributes = [])
 */
class TireSizeFactory extends Factory
{
    protected $model = TireSize::class;

    public function definition(): array
    {
        return [
            'tire_width_id' => TireWidth::factory(),
            'tire_height_id' => TireHeight::factory(),
            'tire_diameter_id' => TireDiameter::factory(),
            'active' => true,
            'is_moderated' => true,
        ];
    }
}
