<?php

namespace Database\Factories\Report\Feature;

use App\Models\Report\Feature\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        return [
            'type' => Feature::TYPE_GROUND,
            'type_field' => Feature::TYPE_FIELD_INT,
            'active' => true,
            'position' => 1,
        ];
    }
}

