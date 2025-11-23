<?php

namespace Database\Factories\Orders;

use App\Models\MoveSize;
use App\Models\Order\MobileEstimate;
use Database\Factories\BaseFactory;

class MobileEstimateFactory extends BaseFactory
{
    protected $model = MobileEstimate::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'deleted_at' => null,
        ];
    }
}

