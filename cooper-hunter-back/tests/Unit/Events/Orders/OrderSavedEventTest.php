<?php

namespace Tests\Unit\Events\Orders;

use App\Events\Orders\OrderSavedEvent;
use App\Models\Orders\OrderStatusHistory;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;

class OrderSavedEventTest extends TestCase
{
    use DatabaseTransactions;
    use OrderCreateTrait;

    /**
     * @throws Exception
     */
    public function test_saved_status_history(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $order = $this->withoutCreatedEvent()
            ->setOrderTechnician($user)
            ->createCreatedOrder();

        event(new OrderSavedEvent($order));

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
                'status' => $order->status,
                'changer_id' => $user->id
            ]
        );
    }
}
