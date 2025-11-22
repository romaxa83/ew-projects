<?php

namespace Tests\Unit\Events\Listeners\Inventories\Inventories;

use App\Events\Events\Inventories\Inventories\UpdateInventoryEvent;
use App\Events\Listeners\Inventories\Inventories\SyncEComUpdateInventoryListener;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryCreateCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class SyncEComUpdateInventoryListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_send_data_as_update()
    {
        $stub = $this->createStub(InventoryExistsCommand::class);
        $stub->method('exec')->willReturn(true);

        // Create a mock
        $mock = Mockery::mock(InventoryUpdateCommand::class);
        $mockCreate = Mockery::mock(InventoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();
        $mockCreate->shouldReceive('exec')->never();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = new UpdateInventoryEvent($model);
        $listener = new SyncEComUpdateInventoryListener($mock, $mockCreate, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function success_send_data_as_create()
    {
        $stub = $this->createStub(InventoryExistsCommand::class);
        $stub->method('exec')->willReturn(false);

        // Create a mock
        $mock = Mockery::mock(InventoryUpdateCommand::class);
        $mockCreate = Mockery::mock(InventoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();
        $mockCreate->shouldReceive('exec')->once();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = new UpdateInventoryEvent($model);
        $listener = new SyncEComUpdateInventoryListener($mock, $mockCreate, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_request_if_send_field_false()
    {
        $stub = $this->createStub(InventoryExistsCommand::class);
        $stub->method('exec')->willReturn(true);

        // Create a mock
        $mock = Mockery::mock(InventoryUpdateCommand::class);
        $mockCreate = Mockery::mock(InventoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();
        $mockCreate->shouldReceive('exec')->never();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = (new UpdateInventoryEvent($model))->setSendToEcomm(false);
        $listener = new SyncEComUpdateInventoryListener($mock, $mockCreate, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
