<?php

namespace Database\Factories\Inventories\Features;

use App\Models\Inventories\Features\Feature;
use Database\Factories\BaseFactory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\Features\Feature>
 */
class FeatureFactory extends BaseFactory
{
    protected $model = Feature::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->city . random_int(1000, 20000) . fake()->streetName;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'active' => true,
            'multiple' => true,
            'position' => 1,
        ];
    }
}
