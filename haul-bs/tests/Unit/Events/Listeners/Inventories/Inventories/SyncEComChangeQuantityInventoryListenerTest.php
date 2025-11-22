<?php

namespace Tests\Unit\Events\Listeners\Inventories\Inventories;

use App\Events\Events\Inventories\Inventories\ChangeQuantityInventory;
use App\Events\Listeners\Inventories\Inventories\SyncEComChangeQuantityInventoryListener;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryChangeQuantityCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class SyncEComChangeQuantityInventoryListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_send_data()
    {
        // Create a mock
        $mock = Mockery::mock(InventoryChangeQuantityCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = new ChangeQuantityInventory($model);
        $listener = new SyncEComChangeQuantityInventoryListener($mock);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_request_if_send_field_false()
    {
        // Create a mock
        $mock = Mockery::mock(InventoryChangeQuantityCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = (new ChangeQuantityInventory($model))->setSendToEcomm(false);
        $listener = new SyncEComChangeQuantityInventoryListener($mock);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
