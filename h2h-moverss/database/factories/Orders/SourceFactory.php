<?php

namespace Database\Factories\Orders;

use App\Models\Division;
use App\Models\Order;
use Database\Factories\BaseFactory;

class SourceFactory extends BaseFactory
{
    protected $model = Order\Source::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $division = Division::factory()->create();

        return [
            'title' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
            'division_ids' => [$division->id],
            'deleted_at' => null,
        ];
    }
}
