<?php

namespace Tests\Feature\Api\Orders\Inspections;

use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Models\History\History;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use App\Models\Users\User;
use App\Services\Events\Order\OrderEventService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;

class OnlineSignInspectionTest extends OrderTestCase
{
    use OrderFactoryHelper;
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_get_public_bol()
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
                'pickup_customer_refused_to_sign' => true,
                'pickup_customer_not_available' => true
            ]
        );

        $this->getJson(
            route('orders.public-bol', $order->public_token)
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'load_id',
                    'state',
                    'pickup_contact',
                    'pickup_date_actual',
                    'delivery_contact',
                    'delivery_date_actual',
                    'vehicles',
                    'need_signature'
                ]
            ])
            ->assertJsonFragment(['need_signature' => null]);
    }

    public function test_get_public_bol_can_pickup_sign()
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
                'pickup_customer_refused_to_sign' => true,
                'pickup_customer_not_available' => true
            ]
        );

        /**@var OrderSignature $signature*/
        $signature = OrderSignature::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->authenticatedUser->id,
            'inspection_location' => Order::LOCATION_PICKUP
        ]);

        $this->getJson(
            route('orders.public-bol', $signature->signature_token)
        )
            ->assertOk()
            ->assertJsonFragment(['need_signature' => Order::LOCATION_PICKUP]);
    }

    public function test_get_public_bol_can_delivery_sign()
    {
        $this->loginAsCarrierSuperAdmin();
        $deliveryInspection = Inspection::factory()->create();

        $dispatcher = $this->dispatcherFactory();

        /**@var Order $order*/
        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'delivery_inspection_id' => $deliveryInspection->id,
                'has_delivery_signature' => true,
                'has_delivery_inspection' => true,
                'delivery_customer_refused_to_sign' => true,
                'delivery_customer_not_available' => true
            ]
        );

        /**@var OrderSignature $signature*/
        $signature = OrderSignature::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->authenticatedUser->id,
            'inspection_location' => Order::LOCATION_DELIVERY
        ]);

        $this->getJson(
            route('orders.public-bol', $signature->signature_token)
        )
            ->assertOk()
            ->assertJsonFragment(['need_signature' => Order::LOCATION_DELIVERY]);
    }

    public function test_get_public_bol_w_expired_sign_link()
    {
        $this->loginAsCarrierSuperAdmin();
        $deliveryInspection = Inspection::factory()->create();

        $dispatcher = $this->dispatcherFactory();

        /**@var Order $order*/
        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'dispatcher_id' => $dispatcher->id,
                'delivery_inspection_id' => $deliveryInspection->id,
                'has_delivery_signature' => true,
                'has_delivery_inspection' => true,
                'delivery_customer_refused_to_sign' => true,
                'delivery_customer_not_available' => true
            ]
        );

        /**@var OrderSignature $signature*/
        $signature = OrderSignature::factory()->create([
            'order_id' => $order->id,
            'user_id' => $this->authenticatedUser->id,
            'inspection_location' => Order::LOCATION_DELIVERY,
            'created_at' => Carbon::now()->subSeconds(
                config('orders.inspection.signature_bol_link_life') + 20
            )->timestamp
        ]);

        $this->getJson(
            route('orders.public-bol', $signature->signature_token)
        )
            ->assertNotFound();
    }

    public function test_sign_inspection()
    {
        $deliveryInspection = Inspection::factory()->create();

        $accountant = $this->userFactory(User::ACCOUNTANT_ROLE);
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(['owner_id' => $dispatcher->id]);

        /**@var Order $order*/
        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'delivery_inspection_id' => $deliveryInspection->id,
                'has_delivery_signature' => true,
                'has_delivery_inspection' => true,
                'delivery_customer_refused_to_sign' => true,
                'delivery_customer_not_available' => true
            ]
        );

        /**@var OrderSignature $signature*/
        $signature = OrderSignature::factory()->create([
            'order_id' => $order->id,
            'user_id' => $accountant->id,
            'inspection_location' => Order::LOCATION_DELIVERY
        ]);

        Event::fake([UpdateOrderBroadcast::class]);

        $this->postJson(
            route('orders.sign-public-bol', $signature->signature_token),
            [
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'signed_time' => Carbon::now()->format('m/d/Y g:i A'),
                'inspection_agree' => true,
                'sign_file' => UploadedFile::fake()->image('signature.png')
            ]
        );

        $order->refresh();
        $signature->refresh();

        $this->assertNotNull($signature->first_name);
        $this->assertNotNull($signature->last_name);
        $this->assertTrue($signature->signed);

        $this->assertFalse($order->delivery_customer_not_available);
        $this->assertFalse($order->delivery_customer_refused_to_sign);
        $this->assertEquals($order->delivery_customer_full_name, $signature->first_name . ' ' . $signature->last_name);

        $this->assertDatabaseHas(
            History::class,
            [
                'model_type' => Order::class,
                'model_id' => $order->id,
                'message' => OrderEventService::HISTORY_MESSAGE_SIGNED_INSPECTION,
                'meta' => json_encode([
                    'first_name' => $signature->first_name,
                    'last_name' => $signature->last_name,
                    'email' => $signature->email,
                    'location' => $signature->inspection_location
                ]),
                'performed_at' => $signature->signed_time->format('Y-m-d H:i:s')
            ]
        );

        Event::assertDispatched(UpdateOrderBroadcast::class);
    }
}
