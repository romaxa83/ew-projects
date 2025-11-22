<?php

namespace Database\Factories\Orders\Dealer;

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\PackingSlip;
use App\Models\Orders\Dealer\PackingSlipSerialNumber;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|PackingSlipSerialNumber[]|PackingSlipSerialNumber create(array $attributes = [])
 */
class PackingSlipSerialNumberFactory extends BaseFactory
{
    protected $model = PackingSlipSerialNumber::class;

    public function definition(): array
    {
        return [
            'packing_slip_id' => PackingSlip::factory(),
            'product_id' => Product::factory(),
            'serial_number' => $this->faker->creditCardNumber
        ];
    }
}
