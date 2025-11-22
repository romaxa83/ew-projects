<?php

namespace Feature\Http\Api\V1\Inventories\Feature\Crud;

use App\Models\Inventories\Features\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected FeatureBuilder $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Feature */
        $m_1 = $this->featureBuilder->name('aaaaa')->create();
        $m_2 = $this->featureBuilder->name('zzzzz')->create();
        $m_3 = $this->featureBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories.feature'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'position',
                        'multiple',
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

        $this->featureBuilder->create();
        $this->featureBuilder->create();
        $this->featureBuilder->create();

        $this->getJson(route('api.v1.inventories.feature', ['page' => 2]))
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

        $this->featureBuilder->create();
        $this->featureBuilder->create();
        $this->featureBuilder->create();

        $this->getJson(route('api.v1.inventories.feature', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.inventories.feature'))
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

        /** @var $m_1 Feature */
        $m_1 = $this->featureBuilder->name('aaaaa')->create();
        $this->featureBuilder->name('zzzzz')->create();
        $this->featureBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories.feature', [
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
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.feature'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.feature'));

        self::assertUnauthenticatedMessage($res);
    }
}
