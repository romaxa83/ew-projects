<?php

namespace Tests\Unit\Events\Listeners\Inventories\Categories;

use App\Events\Events\Inventories\Categories\DeleteCategoryEvent;
use App\Events\Listeners\Inventories\Categories\SyncEComDeleteCategoryListener;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\Commands\Category\CategoryDeleteCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryExistsCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class SyncEComDeleteCategoryListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->categoryBuilder = resolve(CategoryBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $stub = $this->createStub(CategoryExistsCommand::class);
        $stub->method('exec')->willReturn(true);

        // Create a mock
        $mock = Mockery::mock(CategoryDeleteCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $event = new DeleteCategoryEvent($model);
        $listener = new SyncEComDeleteCategoryListener($mock, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }

    /** @test */
    public function not_request_delete_if_exist_false()
    {
        $stub = $this->createStub(CategoryExistsCommand::class);
        $stub->method('exec')->willReturn(false);

        // Create a mock
        $mock = Mockery::mock(CategoryDeleteCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->never();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $event = new DeleteCategoryEvent($model);
        $listener = new SyncEComDeleteCategoryListener ($mock, $stub);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
