<?php

namespace Tests\Feature\Api\Orders\SendDocs;

use App\Models\History\History;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use App\Models\Users\User;
use App\Notifications\Orders\SendSignatureLink;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Notification;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;

class SendBolForSignatureTest extends OrderTestCase
{
    use OrderFactoryHelper;
    use  UserFactoryHelper;

    public function test_send_signature_link_success(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $pickupInspection = Inspection::factory()->create();

        $dispatcher = $this->dispatcherFactory();

        /**@var Order $order */
        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'pickup_inspection_id' => $pickupInspection->id,
                'has_pickup_signature' => true,
                'has_pickup_inspection' => true,
                'pickup_customer_refused_to_sign' => true,
                'pickup_customer_not_available' => true
            ]
        );

        Notification::fake();

        $email = $this->faker->email;

        $this->postJson(
            route('orders.send-signature-link', $order),
            [
                'email' => $email,
                'inspection_location' => Order::LOCATION_PICKUP
            ]
        )
            ->assertNoContent();

        Notification::assertSentTo(new AnonymousNotifiable(), SendSignatureLink::class);

        $this->assertDatabaseHas(
            OrderSignature::class,
            [
                'order_id' => $order->id,
                'user_id' => $this->authenticatedUser->id,
                'email' => $email,
                'inspection_location' => Order::LOCATION_PICKUP
            ]
        );

        $this->assertDatabaseHas(
            History::class,
            [
                'model_type' => Order::class,
                'model_id' => $order->id,
                'user_id' => $this->authenticatedUser->id,
                'user_role' => User::SUPERADMIN_ROLE,
                'message' => 'history.sent_signature_link',
                'meta' => json_encode([
                    'location' => Order::LOCATION_PICKUP,
                    'full_name' => $this->authenticatedUser->full_name,
                    'email_sender' => $this->authenticatedUser->email,
                    'email_recipient' => $email
                ])
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function test_send_signature_link_w_o_inspection(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $dispatcher = $this->dispatcherFactory();

        /**@var Order $order*/
        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id
            ]
        );

        Notification::fake();

        $email = $this->faker->email;

        $this->postJson(
            route('orders.send-signature-link', $order),
            [
                'email' => $email,
                'inspection_location' => Order::LOCATION_PICKUP
            ]
        )
            ->assertStatus(Response::HTTP_FAILED_DEPENDENCY);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendSignatureLink::class);
    }

    public function test_send_signature_link_w_signature(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $pickupInspection = Inspection::factory()->create();

        $dispatcher = $this->dispatcherFactory();

        /**@var Order $order*/
        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'pickup_inspection_id' => $pickupInspection->id,
                'has_pickup_signature' => true,
                'has_pickup_inspection' => true,
                'pickup_customer_refused_to_sign' => false,
                'pickup_customer_not_available' => false
            ]
        );

        Notification::fake();

        $email = $this->faker->email;

        $this->postJson(
            route('orders.send-signature-link', $order),
            [
                'email' => $email,
                'inspection_location' => Order::LOCATION_PICKUP
            ]
        )
            ->assertStatus(Response::HTTP_FAILED_DEPENDENCY);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendSignatureLink::class);
    }
}
