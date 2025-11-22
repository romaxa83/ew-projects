<?php


namespace Api\Orders;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\ElasticsearchClear;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\Helpers\Traits\UserFactoryHelper;

class SameVinLoadIdTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    public function test_duplicate()
    {
        $this->loginAsCarrierSuperAdmin();

        $load_id = 'load id 123';
        $vin = 'abcdefg1234567890';
        $dispatcher = $this->dispatcherFactory();
        $orderData = $this->getRequiredFields() + $this->order_fields_create;
        $orderData['status'] = 10;
        $orderData['load_id'] = $load_id;
        $orderData['dispatcher_id'] = $dispatcher->id;
        $orderData['vehicles'][0]['vin'] = $vin;

        // check no load id duplicate exist

        $this->getJson(
            route(
                'orders.same-load-id',
                [
                    'load_id' => $load_id,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // check no vin duplicate exist

        $this->getJson(
            route(
                'orders.same-vin',
                [
                    'vin' => $vin,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // check if order visible
        $orderId = $this->postJson(
            route('orders.store'),
            $orderData
        )
            ->assertCreated()->json('data.id');
        $this->getJson(route('orders.show', $orderId))
            ->assertOk();

        // check load id duplicate exist

        $this->getJson(
            route(
                'orders.same-load-id',
                [
                    'load_id' => $load_id,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_id', $orderId)
            ->assertJsonPath('data.0.load_id', $load_id);

        // check vin duplicate exist

        $this->getJson(
            route(
                'orders.same-vin',
                [
                    'vin' => $vin,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_id', $orderId)
            ->assertJsonPath('data.0.load_id', $load_id);

        // check no load id duplicate exist for created order

        $this->getJson(
            route(
                'orders.same-load-id',
                [
                    'order_id' => $orderId,
                    'load_id' => $load_id,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // check no vin duplicate exist for created order

        $this->getJson(
            route(
                'orders.same-vin',
                [
                    'order_id' => $orderId,
                    'vin' => $vin,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
