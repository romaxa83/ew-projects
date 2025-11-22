<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Category;

use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\Commands\Brand\BrandDeleteCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryDeleteCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    public function setUp(): void
    {
        $this->categoryBuilder = resolve(CategoryBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_before_request()
    {
        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        /** @var $command CategoryDeleteCommand */
        $command = resolve(CategoryDeleteCommand::class);

        $data = ['id' => $model->id];

        $res = $command->beforeRequestForData($data);

        $this->assertEquals($res, $data);
    }

    /** @test */
    public function check_before_request_not_id()
    {
        /** @var $command CategoryDeleteCommand */
        $command = resolve(CategoryDeleteCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage("For this command [CategoryDeleteCommand] you need to pass 'id' to uri");

        $command->beforeRequestForData($data);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        /** @var $command CategoryDeleteCommand */
        $command = resolve(CategoryDeleteCommand::class);

        $this->assertEquals($command->getUri(['id' => $model->id]), str_replace('{id}', $model->id, config("requests.e_com.paths.category.delete")));
    }
}
