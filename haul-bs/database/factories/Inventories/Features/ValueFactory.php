<?php

namespace Database\Factories\Inventories\Features;

use App\Models\Inventories\Features\Value;
use Database\Factories\BaseFactory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\Features\Value>
 */
class ValueFactory extends BaseFactory
{
    protected $model = Value::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->city . random_int(1000, 20000) . fake()->streetName;

        return [
            'feature_id' => FeatureFactory::new(),
            'name' => $name,
            'slug' => Str::slug($name),
            'active' => true,
            'position' => 1,
        ];
    }
}
