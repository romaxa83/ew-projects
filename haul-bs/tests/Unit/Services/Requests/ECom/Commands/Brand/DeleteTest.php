<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Brand;

use App\Models\Inventories\Brand;
use App\Services\Requests\ECom\Commands\Brand\BrandDeleteCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;

    public function setUp(): void
    {
        $this->brandBuilder = resolve(BrandBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_before_request()
    {
        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        /** @var $command BrandDeleteCommand */
        $command = resolve(BrandDeleteCommand::class);

        $data = ['id' => $model->id];

        $res = $command->beforeRequestForData($data);

        $this->assertEquals($res, $data);
    }

    /** @test */
    public function check_before_request_not_id()
    {
        /** @var $command BrandDeleteCommand */
        $command = resolve(BrandDeleteCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage("For this command [BrandDeleteCommand] you need to pass 'id' to uri");

        $command->beforeRequestForData($data);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        /** @var $command BrandDeleteCommand */
        $command = resolve(BrandDeleteCommand::class);
        $this->assertEquals(
            $command->getUri(['id' => $model->id]),
            str_replace('{id}', $model->id, config("requests.e_com.paths.brand.delete"))
        );
    }
}
