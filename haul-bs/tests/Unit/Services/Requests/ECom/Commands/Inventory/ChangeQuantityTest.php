<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Inventory;

use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryChangeQuantityCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class ChangeQuantityTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        $this->inventoryBuilder = resolve(InventoryBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $command InventoryChangeQuantityCommand */
        $command = resolve(InventoryChangeQuantityCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['quantity'], $model->quantity);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $command InventoryChangeQuantityCommand */
        $command = resolve(InventoryChangeQuantityCommand::class);

        $this->assertEquals(
            $command->getUri(['guid' => $model->id]),
            str_replace('{id}', $model->id, config("requests.e_com.paths.inventory.update_quantity"))
        );
    }

    /** @test */
    public function fail_uri()
    {
        /** @var $command InventoryChangeQuantityCommand */
        $command = resolve(InventoryChangeQuantityCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage(
            "For this command [InventoryChangeQuantityCommand] you need to pass 'guid' to uri"
        );

        $command->getUri($data);
    }
}

