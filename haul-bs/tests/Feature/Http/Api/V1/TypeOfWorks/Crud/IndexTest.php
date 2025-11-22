<?php

namespace Tests\Feature\Http\Api\V1\TypeOfWorks\Crud;

use App\Models\TypeOfWorks\TypeOfWork;
use Tests\Builders\TypeOfWorks\TypeOfWorkInventoryBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected TypeOfWorkBuilder $typeOfWorkBuilder;
    protected TypeOfWorkInventoryBuilder $typeOfWorkInventoryBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->typeOfWorkBuilder = resolve(TypeOfWorkBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->typeOfWorkInventoryBuilder = resolve(TypeOfWorkInventoryBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->typeOfWorkBuilder->name('aaaaa')->create();
        $m_2 = $this->typeOfWorkBuilder->name('zzzzz')->create();
        $m_3 = $this->typeOfWorkBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.type-of-works'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'duration',
                        'hourly_rate',
                        'estimated_amount',
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

        $this->typeOfWorkBuilder->create();
        $this->typeOfWorkBuilder->create();
        $this->typeOfWorkBuilder->create();

        $this->getJson(route('api.v1.type-of-works', ['page' => 2]))
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

        $this->typeOfWorkBuilder->create();
        $this->typeOfWorkBuilder->create();
        $this->typeOfWorkBuilder->create();

        $this->getJson(route('api.v1.type-of-works', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.type-of-works'))
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
    public function success_filter_by_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $i_1 = $this->inventoryBuilder->create();
        $i_2 = $this->inventoryBuilder->create();

        /** @var $m_1 TypeOfWork */
        $m_1 = $this->typeOfWorkBuilder->name('aaaaa')->create();
        $m_2 = $this->typeOfWorkBuilder->name('zzzzz')->create();
        $m_3 = $this->typeOfWorkBuilder->name('bbbbb')->create();

        $this->typeOfWorkInventoryBuilder->inventory($i_1)->work($m_1)->create();
        $this->typeOfWorkInventoryBuilder->inventory($i_1)->work($m_3)->create();
        $this->typeOfWorkInventoryBuilder->inventory($i_2)->work($m_2)->create();

        $this->getJson(route('api.v1.type-of-works', [
            'inventory_id' => $i_1->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 TypeOfWork */
        $m_1 = $this->typeOfWorkBuilder->name('aaaaa')->create();
        $m_2 = $this->typeOfWorkBuilder->name('zzzzz')->create();
        $m_3 = $this->typeOfWorkBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.type-of-works', [
            'search' => 'aaaa'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_name_desc()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 TypeOfWork */
        $m_1 = $this->typeOfWorkBuilder->name('aaaaa')->create();
        $m_2 = $this->typeOfWorkBuilder->name('zzzzz')->create();
        $m_3 = $this->typeOfWorkBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.type-of-works', [
            'order_type' => 'desc'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id,],
                    ['id' => $m_3->id,],
                    ['id' => $m_1->id,],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.type-of-works'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.type-of-works'));

        self::assertUnauthenticatedMessage($res);
    }
}
