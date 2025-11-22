<?php

namespace Database\Factories\Suppliers;

use App\Models\Suppliers\Supplier;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Suppliers\Supplier>
 */
class SupplierFactory extends BaseFactory
{
    protected $model = Supplier::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'url' => fake()->url,
        ];
    }
}
