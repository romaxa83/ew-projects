<?php

namespace Tests\Feature\Api\Orders\Inspections;

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;

class NoneInspectionTest extends OrderTestCase
{
    use OrderFactoryHelper;
    use DatabaseTransactions;

    public function test_it_none_inspection_w_o_file(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(['carrier_id' => $dispatcher->carrier_id, 'owner_id' => $dispatcher->id]);

        $this->loginAsCarrierDriver($driver);

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'inspection_type' => Order::INSPECTION_TYPE_NONE
            ]
        );

        $this->assertFalse($order->isPickedUp());

        $this->postJson(
            route('v2.carrier-mobile.orders.complete-inspection', $order),
            [
                'inspection_type' => Order::LOCATION_PICKUP
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.has_delivery_inspection', false)
            ->assertJsonPath('data.has_delivery_signature', false);

        $order->refresh();

        $this->assertTrue($order->isPickedUp());

        $this->assertFalse($order->isDelivered());

        $this->postJson(
            route('v2.carrier-mobile.orders.complete-inspection', $order),
            [
                'inspection_type' => Order::LOCATION_DELIVERY
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.has_delivery_inspection', true)
            ->assertJsonPath('data.has_delivery_signature', true);

        $order->refresh();

        $this->assertTrue($order->isDelivered());
    }

    public function test_it_none_inspection_w_file(): void
    {
        $dispatcher = $this->dispatcherFactory();
        $driver = $this->driverFactory(['carrier_id' => $dispatcher->carrier_id, 'owner_id' => $dispatcher->id]);

        $this->loginAsCarrierDriver($driver);

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
                'inspection_type' => Order::INSPECTION_TYPE_NONE_W_FILE
            ]
        );

        $this->postJson(
            route('v2.carrier-mobile.orders.complete-inspection', $order),
            [
                'inspection_type' => Order::LOCATION_PICKUP
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.source.parameter', 'bol_file');

        $this->assertFalse($order->isPickedUp());

        $bol = UploadedFile::fake()->create('bol.pdf');

        $this->postJson(
            route('v2.carrier-mobile.orders.complete-inspection', $order),
            [
                'inspection_type' => Order::LOCATION_PICKUP,
                'bol_file' => $bol
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.has_delivery_inspection', false)
            ->assertJsonPath('data.has_delivery_signature', false);

        $order->refresh();

        $this->assertTrue($order->isPickedUp());

        $this->assertFalse($order->isDelivered());

        $bol = UploadedFile::fake()->create('bol.pdf');

        $this->postJson(
            route('v2.carrier-mobile.orders.complete-inspection', $order),
            [
                'inspection_type' => Order::LOCATION_DELIVERY,
                'bol_file' => $bol
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.has_pickup_inspection', true)
            ->assertJsonPath('data.has_pickup_signature', true)
            ->assertJsonPath('data.has_delivery_inspection', true)
            ->assertJsonPath('data.has_delivery_signature', true);

        $order->refresh();

        $this->assertTrue($order->isDelivered());
    }
}
