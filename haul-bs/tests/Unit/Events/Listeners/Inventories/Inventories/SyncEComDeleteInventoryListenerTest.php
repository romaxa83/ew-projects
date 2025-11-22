<?php

namespace Tests\Unit\Events\Listeners\Inventories\Inventories;

use App\Events\Events\Inventories\Inventories\DeleteInventoryEvent;
use App\Events\Listeners\Inventories\Inventories\SyncEComDeleteInventoryListener;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryDeleteCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class SyncEComDeleteInventoryListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $stub = $this->createStub(InventoryExistsCommand::class);
        $stub->method('exec')->willReturn(true);

        // Create a mock
        $mock = Mockery::mock(InventoryDeleteCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = new DeleteInventoryEvent($model);
        $listener = new SyncEComDeleteInventoryListener ($mock, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_request_delete_if_exist_false()
    {
        $stub = $this->createStub(InventoryExistsCommand::class);
        $stub->method('exec')->willReturn(false);

        // Create a mock
        $mock = Mockery::mock(InventoryDeleteCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = new DeleteInventoryEvent($model);
        $listener = new SyncEComDeleteInventoryListener ($mock, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_request_delete_if_send_false()
    {
        $stub = $this->createStub(InventoryExistsCommand::class);
        $stub->method('exec')->willReturn(true);

        // Create a mock
        $mock = Mockery::mock(InventoryDeleteCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = (new DeleteInventoryEvent($model))->setSendToEcomm(false);
        $listener = new SyncEComDeleteInventoryListener ($mock, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
