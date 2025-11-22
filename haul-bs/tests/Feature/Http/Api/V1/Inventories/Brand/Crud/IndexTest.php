<?php

namespace Feature\Http\Api\V1\Inventories\Brand\Crud;

use App\Models\Inventories\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->brandBuilder = resolve(BrandBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Brand */
        $m_1 = $this->brandBuilder->name('aaaaa')->create();
        $m_2 = $this->brandBuilder->name('zzzzz')->create();
        $m_3 = $this->brandBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories.brand'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'hasRelatedEntities',
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

        $this->brandBuilder->create();
        $this->brandBuilder->create();
        $this->brandBuilder->create();

        $this->getJson(route('api.v1.inventories.brand', ['page' => 2]))
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

        $this->brandBuilder->create();
        $this->brandBuilder->create();
        $this->brandBuilder->create();

        $this->getJson(route('api.v1.inventories.brand', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.inventories.brand'))
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

        /** @var $m_1 Brand */
        $m_1 = $this->brandBuilder->name('aaaaa')->create();
        $this->brandBuilder->name('zzzzz')->create();
        $this->brandBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories.brand', [
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

        $res = $this->getJson(route('api.v1.inventories.brand'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.brand'));

        self::assertUnauthenticatedMessage($res);
    }
}
