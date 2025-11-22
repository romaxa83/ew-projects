<?php

namespace Tests\Unit\Events\Listeners\Inventories\Categories;

use App\Events\Events\Inventories\Categories\CreateCategoryEvent;
use App\Events\Listeners\Inventories\Categories\SyncEComCreateCategoryListener;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\Commands\Category\CategoryCreateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class SyncEComCreateInventoryListenerTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->categoryBuilder = resolve(CategoryBuilder::class);
    }

    /** @test */
    public function success_send_data()
    {
        // Create a mock
        $mock = Mockery::mock(CategoryCreateCommand::class);

        // Define expectation
        $mock->shouldReceive('exec')->once();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $event = new CreateCategoryEvent($model);
        $listener = new SyncEComCreateCategoryListener($mock);
        $listener->handle($event);

        // Close Mockery
        Mockery::close();

        $this->assertTrue(true);
    }
}
