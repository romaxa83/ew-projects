<?php

namespace Tests\Unit\Events\Listeners\Inventories\Inventories;

use App\Events\Events\Inventories\Inventories\CreateInventoryEvent;
use App\Events\Listeners\Inventories\Inventories\SyncEComCreateInventoryListener;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryCreateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class SyncEComCreateInventoryListenerTest extends TestCase
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
        $mock = Mockery::mock(InventoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = new CreateInventoryEvent($model);
        $listener = new SyncEComCreateInventoryListener ($mock);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_request_if_send_field_false()
    {
        // Create a mock
        $mock = Mockery::mock(InventoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $event = (new CreateInventoryEvent($model))->setSendToEcomm(false);
        $listener = new SyncEComCreateInventoryListener ($mock);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
