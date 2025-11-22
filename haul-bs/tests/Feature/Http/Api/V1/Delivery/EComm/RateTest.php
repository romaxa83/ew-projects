<?php

namespace Feature\Http\Api\V1\Delivery\EComm;

use App\Enums\Tags\TagType;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class RateTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success()
    {
        $this->markTestSkipped();
        $model = $this->inventoryBuilder->setData([
            'weight' => 10,
            'length' => 10,
            'width' => 30,
            'height' => 30,
        ])->create();

        $result = $this->postJsonEComm(route('api.v1.e_comm.delivery.rate'), [
            'inventories' => [['id' => $model->id, 'count' => 1]],
            'zip' => '60609',
            'address' => '1323 W 47th St',
            'state' => 'IL',
            'city' => 'Chicago',
        ])->assertOk();

        $this->assertCount(4, $result->json('data'));
    }
}
