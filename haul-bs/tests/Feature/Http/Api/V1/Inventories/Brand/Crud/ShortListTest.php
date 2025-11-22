<?php

namespace Feature\Http\Api\V1\Inventories\Brand\Crud;

use App\Http\Requests\Inventories\Brand\BrandShortListRequest;
use App\Models\Inventories\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->brandBuilder = resolve(BrandBuilder::class);
    }

    /** @test */
    public function success_list_limit()
    {
        $this->loginUserAsSuperAdmin();

        Brand::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.inventories.brand.shortlist', [
            'search' => 'alex',
        ]))
            ->assertJsonCount(BrandShortListRequest::DEFAULT_LIMIT, 'data')
        ;
    }

    /** @test */
    public function success_list_by_limit()
    {
        $this->loginUserAsSuperAdmin();

        Brand::factory()->count(50)->create(['name' => 'Alex']);

        $this->getJson(route('api.v1.inventories.brand.shortlist', [
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

        /** @var $m_1 Brand */
        $m_1 = $this->brandBuilder->create();

        $this->brandBuilder->create();
        $this->brandBuilder->create();

        $this->getJson(route('api.v1.inventories.brand.shortlist', [
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

        /** @var $m_1 Brand */
        $m_1 = $this->brandBuilder->name('Alen')->create();
        $m_2 = $this->brandBuilder->name('Alex')->create();
        $this->brandBuilder->name('Tommy')->create();

        $this->getJson(route('api.v1.inventories.brand.shortlist', [
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

        $this->getJson(route('api.v1.inventories.brand.shortlist', [
            'search' => '555'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.brand.shortlist', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.brand.shortlist', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
