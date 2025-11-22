<?php

namespace Database\Factories\Orders\Dealer;

use App\Models\Orders\Dealer\Dimensions;
use App\Models\Orders\Dealer\PackingSlip;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Dimensions[]|Dimensions create(array $attributes = [])
 */
class DimensionsFactory extends BaseFactory
{
    protected $model = Dimensions::class;

    public function definition(): array
    {
        return [
            'packing_slip_id' => PackingSlip::factory(),
            'pallet' => $this->faker->numberBetween(6,10),
            'box_qty' => $this->faker->numberBetween(10,100),
            'type' => 'box',
            'weight' => $this->faker->randomFloat(2, 101, 200),
            'width' => $this->faker->randomFloat(2, 101, 200),
            'depth' => $this->faker->randomFloat(2, 101, 200),
            'height' => $this->faker->randomFloat(2, 101, 200),
            'class_freight' => $this->faker->numberBetween(1,120),
        ];
    }
}
