<?php

namespace Database\Factories\Audits;

use App\Models\Audit;
use App\Models\Client;
use App\Models\Order;
use App\User;
use Database\Factories\BaseFactory;

class AuditFactory extends BaseFactory
{
    protected $model = Audit::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $order = Order::factory()->create();

        return [
            'user_type' => User::class,
            'user_id' => User::factory(),
            'order_id' => $order->id,
            'client_id' => Client::factory(),
            'event' => Audit::EVENT_UPDATED,
            'auditable_type' => Order::MORPH_NAME,
            'auditable_id' => $order->id,
            'old_values' => [],
            'new_values' => [],
            'url' => 'https://beta.allymovers.com/orders/1/order',
            'ip_address' => '46.219.104.66',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
            'tags' => null,
            'dispatch_truck_at' => null,
            'is_client_activity' => false,
            'division_id' => null,
        ];
    }
}
