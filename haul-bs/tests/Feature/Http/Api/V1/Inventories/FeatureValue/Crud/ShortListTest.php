<?php

namespace Feature\Http\Api\V1\Inventories\FeatureValue\Crud;

use App\Http\Requests\Inventories\FeatureValue\FeatureValueShortListRequest;
use App\Models\Inventories\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
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
    public function success_list_limit()
    {
        $this->loginUserAsSuperAdmin();

        Value::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'search' => 'alex',
        ]))
            ->assertJsonCount(FeatureValueShortListRequest::DEFAULT_LIMIT, 'data')
        ;
    }

    /** @test */
    public function success_list_by_limit()
    {
        $this->loginUserAsSuperAdmin();

        Value::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'search' => 'alex',
            'limit' => 10,
        ]))
            ->assertJsonCount(10, 'data')
        ;
    }

    /** @test */
    public function success_list_by_id()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Value */
        $m_1 = $this->featureValueBuilder->create();

        $this->featureValueBuilder->create();
        $this->featureValueBuilder->create();

        $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Value */
        $m_1 = $this->featureValueBuilder->name('Alen')->create();
        $m_2 = $this->featureValueBuilder->name('Alex')->create();
        $this->featureValueBuilder->name('Tommy')->create();

        $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'search' => 'ale',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_by_name_and_feature()
    {
        $this->loginUserAsSuperAdmin();

        $f_1 = $this->featureBuilder->create();

        /** @var $m_1 Value */
        $m_1 = $this->featureValueBuilder->name('Alen')->feature($f_1)->create();
        $m_2 = $this->featureValueBuilder->name('Alex')->create();
        $this->featureValueBuilder->name('Tommy')->create();

        $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'search' => 'ale',
            'feature_id' => $f_1->id,
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }


    /** @test */
    public function success_list_by_name_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'search' => '555'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.feature.value.shortlist', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
