<?php

namespace Database\Factories\Orders\Parts;

use App\Enums\Orders\Parts\DeliveryMethod;
use App\Enums\Orders\Parts\DeliveryStatus;
use App\Models\Orders\Parts\Delivery;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Orders\Parts\Delivery>
 */
class DeliveryFactory extends BaseFactory
{
    protected $model = Delivery::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'method' => DeliveryMethod::USPS(),
            'cost' => 12,
            'tracking_number' => '3333333',
            'sent_at' => CarbonImmutable::now()->subDays(30),
            'status' => DeliveryStatus::Sent(),
        ];
    }
}
