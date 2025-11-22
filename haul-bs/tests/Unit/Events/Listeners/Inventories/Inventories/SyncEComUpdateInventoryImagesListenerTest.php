<?php

namespace Tests\Unit\Events\Listeners\Inventories\Inventories;

use App\Events\Listeners\Inventories\Inventories\SyncEComUpdateInventoryImagesListener;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryCreateCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateImagesCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class SyncEComUpdateInventoryImagesListenerTest extends TestCase
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
        $mock = Mockery::mock(InventoryUpdateImagesCommand::class);
        $mockCreate = Mockery::mock(InventoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();
        $mockCreate->shouldReceive('exec')->never();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();
        $media = Media::factory()->for($model, 'model')->setGenerateConversions()->create();
        $conversion = Conversion::create($media);

        $event = new ConversionHasBeenCompleted($media, $conversion);
        $listener = new SyncEComUpdateInventoryImagesListener($mock, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
