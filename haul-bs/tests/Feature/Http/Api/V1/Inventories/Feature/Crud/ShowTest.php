<?php

namespace Feature\Http\Api\V1\Inventories\Feature\Crud;

use App\Models\Inventories\Features\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $f_1 = $this->featureValueBuilder->feature($model)->position(3)->create();
        $f_2 = $this->featureValueBuilder->feature($model)->position(1)->create();

        $this->getJson(route('api.v1.inventories.feature.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'slug' => $model->slug,
                    'position' => $model->position,
                    'multiple' => $model->multiple,
                    'active' => $model->active,
                    'values' => [
                        ['id' => $f_2->id],
                        ['id' => $f_1->id],
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.feature.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.features.feature.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.feature.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.feature.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
