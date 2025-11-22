<?php

namespace Feature\Http\Api\V1\Inventories\FeatureValue\Crud;

use App\Models\Inventories\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureValueBuilder $featureValueBuilder;
    protected FeatureBuilder $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Value */
        $m_1 = $this->featureValueBuilder->name('aaaaa')->create();
        $m_2 = $this->featureValueBuilder->name('zzzzz')->create();
        $m_3 = $this->featureValueBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories.feature.value'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'position',
                        'feature_id',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'to' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->featureValueBuilder->create();
        $this->featureValueBuilder->create();
        $this->featureValueBuilder->create();

        $this->getJson(route('api.v1.inventories.feature.value', ['page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'total' => 3,
                    'to' => null,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_per_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->featureValueBuilder->create();
        $this->featureValueBuilder->create();
        $this->featureValueBuilder->create();

        $this->getJson(route('api.v1.inventories.feature.value', ['per_page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'per_page' => 2,
                    'to' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.feature.value'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 0,
                    'to' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Value */
        $m_1 = $this->featureValueBuilder->name('aaaaa')->create();
        $this->featureValueBuilder->name('zzzzz')->create();
        $this->featureValueBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories.feature.value', [
            'search' => 'aaaaa'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_filter_by_feature_id()
    {
        $this->loginUserAsSuperAdmin();

        $f_1 = $this->featureBuilder->create();
        $f_2 = $this->featureBuilder->create();

        /** @var $m_1 Value */
        $m_1 = $this->featureValueBuilder->name('aaaaa')->feature($f_1)->create();
        $this->featureValueBuilder->name('zzzzz')->feature($f_2)->create();
        $m_2 = $this->featureValueBuilder->name('bbbbb')->feature($f_1)->create();

        $this->getJson(route('api.v1.inventories.feature.value', [
            'feature_id' => $f_1->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.feature.value'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.feature.value'));

        self::assertUnauthenticatedMessage($res);
    }
}
