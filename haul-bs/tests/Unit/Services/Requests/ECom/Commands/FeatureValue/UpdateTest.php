<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\FeatureValue;

use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Services\Requests\ECom\Commands\FeatureValue\FeatureValueDeleteCommand;
use App\Services\Requests\ECom\Commands\FeatureValue\FeatureValueUpdateCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
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
    public function check_prepare_data()
    {
        /** @var $feature Feature */
        $feature = $this->featureBuilder->create();
        /** @var $model Value */
        $model = $this->valueBuilder->feature($feature)->create();

        /** @var $command FeatureValueUpdateCommand */
        $command = resolve(FeatureValueUpdateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['guid'], $model->id);
        $this->assertEquals($res['slug'], $model->slug);
        $this->assertEquals($res['sort'], $model->position);
        $this->assertEquals($res['active'], $model->active);
        $this->assertEquals($res['specification_guid'], $model->feature_id);
        $this->assertEquals($res['translations'][0]['language'], 'en');
        $this->assertEquals($res['translations'][0]['name'], $model->name);
    }

    /** @test */
    public function check_uri()
    {
        //** @var $feature Feature */
        $feature = $this->featureBuilder->create();
        /** @var $model Value */
        $model = $this->valueBuilder->feature($feature)->create();

        /** @var $command FeatureValueUpdateCommand */
        $command = resolve(FeatureValueUpdateCommand::class);

        $this->assertEquals(
            $command->getUri(['guid' => $model->id]),
            str_replace('{id}', $model->id, config("requests.e_com.paths.feature_value.update"))
        );
    }

    /** @test */
    public function fail_uri()
    {
        /** @var $command FeatureValueUpdateCommand */
        $command = resolve(FeatureValueUpdateCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage(
            "For this command [FeatureValueUpdateCommand] you need to pass 'guid' to uri"
        );

        $command->getUri($data);
    }
}
