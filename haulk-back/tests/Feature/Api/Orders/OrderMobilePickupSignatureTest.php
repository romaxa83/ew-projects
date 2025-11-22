<?php

namespace Tests\Feature\Api\Orders;

use App\Events\ModelChanged;
use App\Events\OrderStatusChanged;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Users\User;
use App\Notifications\OrderPickedUp;
use Event;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\Traits\OrderFactoryHelper;

class OrderMobilePickupSignatureTest extends OrderTestCase
{
    use OrderFactoryHelper;

    public function test_it_send_pickup_signature_success_with_order_status_change_event(): void
    {
        Event::fake([OrderStatusChanged::class, ModelChanged::class]);

        $this->test_it_send_pickup_signature_success();

        Event::assertDispatched(OrderStatusChanged::class);
        Event::assertDispatched(ModelChanged::class);
    }

    public function test_it_send_pickup_signature_success(): Order
    {
        $this->loginAsCarrierDriver();

        $inspection = Inspection::factory()->create();
        $dispatcher = User::factory()->create();
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'pickup_inspection_id' => $inspection->id,
            ]
        );

        Payment::factory()->create(
            [
                'order_id' => $order->id,
                'driver_payment_data_sent' => true,
            ]
        );

        $this->postJson(
            route('mobile.orders.pickup-signature', $order),
            [
                'customer_full_name' => 'Customer full name',
                'driver_signature' => UploadedFile::fake()->image('driver_signature.jpg'),
                'customer_signature' => UploadedFile::fake()->image('customer_signature.jpg'),
            ]
        )
            ->assertOk();
        return $order;
    }

    public function test_it_send_pickup_signature_customer_not_available_success(): void
    {
        $this->loginAsCarrierDriver();

        $inspection = Inspection::factory()->create();
        $dispatcher = User::factory()->create();
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'pickup_inspection_id' => $inspection->id,
            ]
        );

        Payment::factory()->create(
            [
                'order_id' => $order->id,
                'driver_payment_data_sent' => true,
            ]
        );

        $this->postJson(
            route('mobile.orders.pickup-signature', $order),
            [
                'customer_not_available' => true,
                'driver_signature' => UploadedFile::fake()->image('driver_signature.jpg'),
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_customer_not_available', true)
            ->assertJsonPath('data.pickup_customer_refused_to_sign', false);
    }

    public function test_it_send_pickup_signature_refused_to_sign_success(): void
    {
        $this->loginAsCarrierDriver();

        $inspection = Inspection::factory()->create();
        $dispatcher = User::factory()->create();
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'pickup_inspection_id' => $inspection->id,
            ]
        );

        Payment::factory()->create(
            [
                'order_id' => $order->id,
                'driver_payment_data_sent' => true,
            ]
        );

        $customer_full_name = 'some name';

        $this->postJson(
            route('mobile.orders.pickup-signature', $order),
            [
                'customer_refused_to_sign' => true,
                'customer_full_name' => $customer_full_name,
                'driver_signature' => UploadedFile::fake()->image('driver_signature.jpg'),
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_customer_full_name', $customer_full_name)
            ->assertJsonPath('data.pickup_customer_not_available', false)
            ->assertJsonPath('data.pickup_customer_refused_to_sign', true);
    }

    public function test_it_send_pickup_signature_with_actual_date(): void
    {
        $this->loginAsCarrierDriver();

        $inspection = Inspection::factory()->create();
        $dispatcher = User::factory()->create();
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'pickup_inspection_id' => $inspection->id,
            ]
        );

        Payment::factory()->create(
            [
                'order_id' => $order->id,
                'driver_payment_data_sent' => true,
            ]
        );

        $timestamp = Carbon::tomorrow()->timestamp;

        $this->postJson(
            route('mobile.orders.pickup-signature', $order),
            [
                'customer_not_available' => true,
                'driver_signature' => UploadedFile::fake()->image('driver_signature.jpg'),
                'actual_date' => $timestamp,
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_customer_not_available', true)
            ->assertJsonPath('data.pickup_customer_refused_to_sign', false)
            ->assertJsonPath('data.pickup_date_actual', $timestamp);
    }

    /**
     * @throws Exception
     */
    public function test_it_send_pickup_signature_success_with_shipper_contact_emails(): void
    {
        Notification::fake();

        $order = $this->test_it_send_pickup_signature_success();

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            OrderPickedUp::class,
            function ($notification, $channel, $notifiable) use ($order) {
                return $notifiable->routes['mail'] === [$order->shipper_contact['email']];
            }
        );
    }
}
