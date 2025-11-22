<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\FeatureValue;

use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use App\Services\Requests\ECom\Commands\FeatureValue\FeatureValueCreateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
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

        /** @var $command FeatureValueCreateCommand */
        $command = resolve(FeatureValueCreateCommand::class);

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
        /** @var $command FeatureValueCreateCommand */
        $command = resolve(FeatureValueCreateCommand::class);

        $this->assertEquals($command->getUri(), config("requests.e_com.paths.feature_value.create"));
    }
}
