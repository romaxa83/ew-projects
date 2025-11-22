<?php

namespace Tests\Feature\Api\Orders\Inspections;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class InspectVinTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_vin_inspection()
    {
        $driver = $this->loginAsCarrierDriver();
        $correctVin = 'abcdefg1234567890';

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'vin' => $correctVin,
            ]
        );

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $vehicle = $order->vehicles->first();

        // send vin in wrong case
        $this->postJson(
            route(
                'mobile.orders.vehicles.inspect-vin',
                [
                    'order' => $order,
                    'vehicle' => $vehicle,
                ]
            ),
            [
                'vin' => mb_convert_case($correctVin, MB_CASE_UPPER),
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_inspection.has_vin_inspection', true);
    }

    public function test_vin_inspection_accepts_any_vin()
    {
        $driver = $this->loginAsCarrierDriver();
        $correctVin = 'abcdefg1234567890';

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id,
                'vin' => $correctVin,
            ]
        );

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertStatus(Response::HTTP_OK);

        $vehicle = $order->vehicles->first();

        // send incorrect vin part
        $vehicle->vin = '567891';
        $vehicle->save();

        $this->postJson(
            route(
                'mobile.orders.vehicles.inspect-vin',
                [
                    'order' => $order,
                    'vehicle' => $vehicle,
                ]
            ),
            [
                'vin' => $correctVin,
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_inspection.has_vin_inspection', true);

        // send correct vin part
        $vehicle->vin = '567890';
        $vehicle->save();

        $this->postJson(
            route(
                'mobile.orders.vehicles.inspect-vin',
                [
                    'order' => $order,
                    'vehicle' => $vehicle,
                ]
            ),
            [
                'vin' => $correctVin,
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_inspection.has_vin_inspection', true);
    }
}
