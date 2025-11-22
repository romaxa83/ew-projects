<?php

namespace Tests\Feature\Api\Orders;

use App\Events\ModelChanged;
use App\Events\OrderStatusChanged;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Notifications\OrderDelivered;
use Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\Traits\OrderFactoryHelper;

class OrderMobileDeliverySignatureTest extends OrderTestCase
{
    use OrderFactoryHelper;

    public function test_it_send_pickup_signature_success_with_order_status_change_event(): void
    {
        Event::fake([OrderStatusChanged::class, ModelChanged::class]);

        $this->test_it_send_delivery_signature_success();

        Event::assertDispatched(OrderStatusChanged::class);
        Event::assertDispatched(ModelChanged::class);
    }

    public function test_it_send_delivery_signature_success(): Order
    {
        $user = $this->loginAsCarrierDriver();

        $order = Order::factory()
            ->pickedUpStatus()
            ->hasPickupInspection()
            ->hasDeliveryInspection()
            ->hasPickupSignature()
            ->has(
                Payment::factory()->driverPaymentDataSent(),
                'payment'
            )
            ->create(['driver_id' => $user->id]);


        $this->postJson(
            route('mobile.orders.delivery-signature', $order),
            [
                'customer_full_name' => 'Customer full name',
                'driver_signature' => UploadedFile::fake()->image('driver_signature.jpg'),
                'customer_signature' => UploadedFile::fake()->image('customer_signature.jpg'),
            ]
        )
            ->assertOk();

        return $order;
    }

    public function test_it_send_delivery_notification(): void
    {
        Notification::fake();

        $order = $this->test_it_send_delivery_signature_success();

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            OrderDelivered::class,
            function ($notification, $channel, $notifiable) use ($order) {
                return $notifiable->routes['mail'] === [$order->shipper_contact['email']];
            }
        );
    }
}
