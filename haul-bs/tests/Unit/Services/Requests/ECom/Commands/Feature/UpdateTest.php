<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Feature;

use App\Models\Inventories\Features\Feature;
use App\Services\Requests\ECom\Commands\Feature\FeatureUpdateCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;

    public function setUp(): void
    {
        $this->featureBuilder = resolve(FeatureBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        /** @var $command FeatureUpdateCommand */
        $command = resolve(FeatureUpdateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['guid'], $model->id);
        $this->assertEquals($res['slug'], $model->slug);
        $this->assertEquals($res['sort'], $model->position);
        $this->assertEquals($res['active'], $model->active);
        $this->assertEquals($res['multiple'], $model->multiple);
        $this->assertEquals($res['translations'][0]['language'], 'en');
        $this->assertEquals($res['translations'][0]['name'], $model->name);
    }

    /** @test */
    public function check_uri()
    {
        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        /** @var $command FeatureUpdateCommand */
        $command = resolve(FeatureUpdateCommand::class);

        $this->assertEquals($command->getUri(['guid' => $model->id]), str_replace('{id}', $model->id, config("requests.e_com.paths.feature.update")));
    }

    /** @test */
    public function fail_uri()
    {
        /** @var $command FeatureUpdateCommand */
        $command = resolve(FeatureUpdateCommand::class);

        $data = [];

        $this->expectException(RequestCommandException::class);
        $this->expectExceptionMessage(
            "For this command [FeatureUpdateCommand] you need to pass 'guid' to uri"
        );

        $command->getUri($data);
    }
}
