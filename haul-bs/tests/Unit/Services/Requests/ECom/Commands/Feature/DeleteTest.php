<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Feature;

use App\Models\Inventories\Features\Feature;
use App\Services\Requests\ECom\Commands\Feature\FeatureDeleteCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;

    public function setUp(): void
    {
        $this->featureBuilder = resolve(FeatureBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_before_request()
    {
        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        /** @var $command FeatureDeleteCommand */
        $command = resolve(FeatureDeleteCommand::class);

        $data = ['id' => $model->id];

        $res = $command->beforeRequestForData($data);

        $this->assertEquals($res, $data);
    }

    /** @test */
    public function check_before_request_not_id()
    {
        /** @var $command FeatureDeleteCommand */
        $command = resolve(FeatureDeleteCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage("For this command [FeatureDeleteCommand] you need to pass 'id' to uri");

        $command->beforeRequestForData($data);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        /** @var $command FeatureDeleteCommand */
        $command = resolve(FeatureDeleteCommand::class);

        $this->assertEquals($command->getUri(['id' => $model->id]), str_replace('{id}', $model->id, config("requests.e_com.paths.feature.delete")));
    }
}
