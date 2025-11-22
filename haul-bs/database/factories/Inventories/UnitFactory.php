<?php

namespace Database\Factories\Inventories;

use App\Models\Inventories\Unit;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventories\Unit>
 */
class UnitFactory extends BaseFactory
{
    protected $model = Unit::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'accept_decimals' => false,
        ];
    }
}
