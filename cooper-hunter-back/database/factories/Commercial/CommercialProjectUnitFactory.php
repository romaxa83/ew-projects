<?php

namespace Database\Factories\Commercial;

use App\Models\Catalog\Products\Product;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectUnit;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|CommercialProjectUnit[]|CommercialProjectUnit create(array $attributes = [])
 */
class CommercialProjectUnitFactory extends BaseFactory
{
    protected $model = CommercialProjectUnit::class;

    public function definition(): array
    {
        return [
            'commercial_project_id' => CommercialProject::factory(),
            'serial_number' => $this->faker->phoneNumber,
            'product_id' => Product::factory()
        ];
    }
}

