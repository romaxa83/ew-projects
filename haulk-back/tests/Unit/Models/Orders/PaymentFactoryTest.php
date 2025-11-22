<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentFactoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_create_payment_success(): void
    {
        $order = Order::factory()->create();

        $this->assertDatabaseMissing(
            Payment::TABLE_NAME,
            [
                'order_id' => $order->id,
            ]
        );

        Payment::factory()->create(['order_id' => $order->id]);

        $this->assertDatabaseHas(
            Payment::TABLE_NAME,
            [
                'order_id' => $order->id,
            ]
        );
    }
}
