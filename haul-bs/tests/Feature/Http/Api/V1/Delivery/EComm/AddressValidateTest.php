<?php

namespace Feature\Http\Api\V1\Delivery\EComm;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class AddressValidateTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function success()
    {
        $this->markTestSkipped();
        $this->postJsonEComm(route('api.v1.e_comm.delivery.address-validate'), [
            'zip' => '60649',
            'address' => 'S Oglesby Ave',
            'state' => 'IL',
            'city' => 'Chicago',
        ])->assertOk()->assertJsonStructure(['data' => [
            'fedex_ground' => [
                'name',
                'amount',
                'date',
                'date_text',
            ],
        ]]);
    }
}
