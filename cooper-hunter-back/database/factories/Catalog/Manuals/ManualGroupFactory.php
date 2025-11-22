<?php

namespace Database\Factories\Catalog\Manuals;

use App\Models\Catalog\Manuals\ManualGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|ManualGroup[]|ManualGroup create(array $attributes = [])
 */
class ManualGroupFactory extends Factory
{
    protected $model = ManualGroup::class;

    public function definition(): array
    {
        return [
            'show_commercial_certified' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
