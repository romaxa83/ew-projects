<?php

namespace Tests\Unit\Events\Listeners\Inventories\Categories;

use App\Events\Events\Inventories\Categories\UpdateCategoryEvent;
use App\Events\Listeners\Inventories\Categories\SyncEComUpdateCategoryListener;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\Commands\Category\CategoryCreateCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryExistsCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryUpdateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class SyncEComUpdateCategoryListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->categoryBuilder = resolve(CategoryBuilder::class);
    }

    /** @test */
    public function success_send_data_as_update()
    {
        $stub = $this->createStub(CategoryExistsCommand::class);
        $stub->method('exec')->willReturn(true);

        // Create a mock
        $mock = Mockery::mock(CategoryUpdateCommand::class);
        $mockCreate = Mockery::mock(CategoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();
        $mockCreate->shouldReceive('exec')->never();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $event = new UpdateCategoryEvent($model);
        $listener = new SyncEComUpdateCategoryListener($mock, $mockCreate, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function success_send_data_as_create()
    {
        $stub = $this->createStub(CategoryExistsCommand::class);
        $stub->method('exec')->willReturn(false);

        // Create a mock
        $mock = Mockery::mock(CategoryUpdateCommand::class);
        $mockCreate = Mockery::mock(CategoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();
        $mockCreate->shouldReceive('exec')->once();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $event = new UpdateCategoryEvent($model);
        $listener = new SyncEComUpdateCategoryListener($mock, $mockCreate, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
