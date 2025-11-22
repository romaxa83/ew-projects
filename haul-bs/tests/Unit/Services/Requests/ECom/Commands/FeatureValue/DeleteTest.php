<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\FeatureValue;

use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Services\Requests\ECom\Commands\FeatureValue\FeatureValueDeleteCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $valueBuilder;

    public function setUp(): void
    {
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->valueBuilder = resolve(FeatureValueBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_before_request()
    {
        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();
        /** @var $model Value */
        $model = $this->valueBuilder->feature($feature)->create();

        /** @var $command FeatureValueDeleteCommand */
        $command = resolve(FeatureValueDeleteCommand::class);

        $data = ['id' => $model->id];

        $res = $command->beforeRequestForData($data);

        $this->assertEquals($res, $data);
    }

    /** @test */
    public function check_before_request_not_id()
    {
        /** @var $command FeatureValueDeleteCommand */
        $command = resolve(FeatureValueDeleteCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage("For this command [FeatureValueDeleteCommand] you need to pass 'id' to uri");

        $command->beforeRequestForData($data);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();
        /** @var $model Value */
        $model = $this->valueBuilder->feature($feature)->create();

        /** @var $command FeatureValueDeleteCommand */
        $command = resolve(FeatureValueDeleteCommand::class);

        $this->assertEquals(
            $command->getUri(['id' => $model->id]),
            str_replace('{id}', $model->id, config("requests.e_com.paths.feature_value.delete"))
        );
    }
}
