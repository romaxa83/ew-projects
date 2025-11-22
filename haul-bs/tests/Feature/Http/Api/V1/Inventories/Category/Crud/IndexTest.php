<?php

namespace Feature\Http\Api\V1\Inventories\Category\Crud;

use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Category */
        $m_1 = $this->categoryBuilder->create();
        $m_2 = $this->categoryBuilder->create();
        $m_3 = $this->categoryBuilder->create();

        $this->getJson(route('api.v1.inventories.category'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'desc',
                        'parent_id',
                        'hasRelatedEntities',
                        'hasChildrenRelatedEntities',
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.category'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_search_by_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Category */
        $m_1 = $this->categoryBuilder->name('aaaaa')->create();
        $m_2 = $this->categoryBuilder->name('zzzzz')->create();
        $m_3 = $this->categoryBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories.category', [
            'search' => 'aaaaa'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'hasRelatedEntities' => false
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

        $res = $this->getJson(route('api.v1.inventories.category'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.category'));

        self::assertUnauthenticatedMessage($res);
    }
}
