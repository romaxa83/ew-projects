<?php

namespace Api\BodyShop\Orders\Payments;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\Payment;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderDeletePaymentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_delete_payment(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $payment = factory(Payment::class)->create(['order_id' => $order->id]);
        $order->setAmounts();

        $this->assertDatabaseHas(
            Payment::TABLE_NAME,
            [
                'id' => $payment->id,
                'order_id' => $order->id,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount,
                'notes' => $payment->notes,
            ],
        );

        $this->deleteJson(route('body-shop.orders.delete-payment', [$order, $payment]))
            ->assertNoContent();

        $this->assertDatabaseMissing(
            Payment::TABLE_NAME,
            [
                'id' => $payment->id,
                'order_id' => $order->id,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount,
                'notes' => $payment->notes,
            ],
        );
    }

    public function test_paid_status_on_delete_payment(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();
        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $order->setAmounts();

        $response = $this->postJson(
            route('body-shop.orders.add-payment', $order),
            [
                'amount' => $order->getAmount(),
                'payment_date' => now()->format('m/d/Y'),
                'payment_method' => Payment::PAYMENT_METHOD_VENMO,
                'notes' => 'test',
            ]
        )
            ->assertCreated();

        $paymentId = $response['data']['id'];
        $order->refresh();
        $this->assertTrue($order->is_paid);
        $this->assertNotNull($order->paid_at);

        $this->deleteJson(route('body-shop.orders.delete-payment', [$order, $paymentId]))
            ->assertNoContent();

        $order->refresh();
        $this->assertFalse($order->is_paid);
        $this->assertNull($order->paid_at);
    }
}
