<?php

namespace Feature\Http\Api\V1\Inventories\Feature\Crud;

use App\Http\Requests\Inventories\Feature\FeatureShortListRequest;
use App\Models\Inventories\Features\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success_list_limit()
    {
        $this->loginUserAsSuperAdmin();

        Feature::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.inventories.feature.shortlist', [
            'search' => 'al',
        ]))
            ->assertJsonCount(FeatureShortListRequest::DEFAULT_LIMIT, 'data')
        ;
    }

    /** @test */
    public function success_list_by_limit()
    {
        $this->loginUserAsSuperAdmin();

        Feature::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.inventories.feature.shortlist', [
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

        /** @var $m_1 Feature */
        $m_1 = $this->featureBuilder->create();

        $this->featureBuilder->create();
        $this->featureBuilder->create();

        $this->getJson(route('api.v1.inventories.feature.shortlist', [
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

        /** @var $m_1 Feature */
        $m_1 = $this->featureBuilder->name('Alen')->create();
        $m_2 = $this->featureBuilder->name('Alex')->create();
        $this->featureBuilder->name('Tommy')->create();

        $this->getJson(route('api.v1.inventories.feature.shortlist', [
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
    public function success_list_by_name_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.feature.shortlist', [
            'search' => '555'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.feature.shortlist', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.feature.shortlist', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
