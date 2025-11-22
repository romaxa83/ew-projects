<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Inventory;

use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryDeleteCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        $this->inventoryBuilder = resolve(InventoryBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_before_request()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $command InventoryDeleteCommand */
        $command = resolve(InventoryDeleteCommand::class);

        $data = ['id' => $model->id];

        $res = $command->beforeRequestForData($data);

        $this->assertEquals($res, $data);
    }

    /** @test */
    public function check_before_request_not_id()
    {
        /** @var $command InventoryDeleteCommand */
        $command = resolve(InventoryDeleteCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage("For this command [InventoryDeleteCommand] you need to pass 'id' to uri");

        $command->beforeRequestForData($data);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $command InventoryDeleteCommand */
        $command = resolve(InventoryDeleteCommand::class);

        $this->assertEquals($command->getUri(['id' => $model->id]), str_replace('{id}', $model->id, config("requests.e_com.paths.inventory.delete")));
    }
}
