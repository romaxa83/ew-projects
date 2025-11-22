<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderFactoryTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_it_create_order_by_factory()
    {
        $orderId = 'new_order_identify';

        $attributes = [
            'load_id' => $orderId,
        ];

        $this->assertDatabaseMissing(Order::TABLE_NAME, $attributes);

        Order::factory()->create($attributes);

        $this->assertDatabaseHas(Order::TABLE_NAME, $attributes);
    }

    public function test_it_create_order_has_payment_relation()
    {
        $order = Order::factory()->create();

        $payment = Payment::factory()->create(['order_id' => $order->id]);

        $this->assertEquals($payment->id, $order->payment->id);
    }
}
