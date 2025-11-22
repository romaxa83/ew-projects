<?php

namespace Feature\Http\Api\V1\Inventories\FeatureValue\Crud;

use App\Models\Inventories\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureValueBuilder $featureValueBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $this->getJson(route('api.v1.inventories.feature.value.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'position' => $model->position,
                    'feature_id' => $model->feature_id,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.feature.value.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.features.value.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.feature.value.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.feature.value.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
