<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Inventory;

use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class ExistsTest extends TestCase
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

        /** @var $command InventoryExistsCommand */
        $command = resolve(InventoryExistsCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res, [
            'guid' => (string)$model->id,
        ]);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        /** @var $command InventoryExistsCommand */
        $command = resolve(InventoryExistsCommand::class);

        $this->assertEquals(
            $command->getUri(),
            config("requests.e_com.paths.inventory.exists")
        );
    }

    /** @test */
    public function check_after_request()
    {
        /** @var $command InventoryExistsCommand */
        $command = resolve(InventoryExistsCommand::class);

        $data['exists'] = true;

        $this->assertTrue($command->afterRequest($data));

        $data['exists'] = false;

        $this->assertFalse($command->afterRequest($data));
    }
}

