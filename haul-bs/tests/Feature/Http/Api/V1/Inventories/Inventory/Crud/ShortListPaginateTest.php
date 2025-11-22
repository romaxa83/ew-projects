<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Crud;

use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class ShortListPaginateTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        for ($i = 50; $i > 0; $i--){
            Inventory::factory()->create(['name' => 'Alex', 'stock_number' => 'stock_number_'.$i]);
        }

        $this->getJson(route('api.v1.inventories.shortlist-paginate', [
            'search' => 'alex',
        ]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'stock_number',
                        'article_number',
                        'price',
                        'price_old',
                        'quantity',
                        'unit' => [
                            'id',
                            'name',
                            'accept_decimals',
                        ],
                        'brand',
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        for ($i = 30; $i > 0; $i--){
            Inventory::factory()->create(['name' => 'Alex', 'stock_number' => 'stock_number_'.$i]);
        }

        $this->getJson(route('api.v1.inventories.shortlist-paginate', ['page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'total' => 30,
                    'to' => 20,
                ]
            ])
        ;
    }
    /** @test */
    public function success_by_per_page()
    {
        $this->loginUserAsSuperAdmin();

        for ($i = 3; $i > 0; $i--){
            Inventory::factory()->create(['name' => 'Alex', 'stock_number' => 'stock_number_'.$i]);
        }

        $this->getJson(route('api.v1.inventories.shortlist-paginate', ['per_page' => 2]))
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
    public function success_list_by_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('Alex')->stock_number('stock_1')->create();
        $m_2 = $this->inventoryBuilder->name('Alex')->stock_number('stock_2')->create();
        $this->inventoryBuilder->name('Tommy')->stock_number('stock_3')->create();

        $this->getJson(route('api.v1.inventories.shortlist-paginate', [
            'search' => 'ale',
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
    public function success_list_by_stock_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('Alex')->stock_number('stock_1')->create();
        $m_2 = $this->inventoryBuilder->name('Alex')->stock_number('stock_2')->create();
        $this->inventoryBuilder->name('Tommy')->stock_number('ero_3')->create();

        $this->getJson(route('api.v1.inventories.shortlist-paginate', [
            'search' => 'stoc',
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
    public function success_list_by_article_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('Alex')->article_number('stock_1')->create();
        $m_2 = $this->inventoryBuilder->name('Alex')->article_number('stock_2')->create();
        $this->inventoryBuilder->name('Tommy')->article_number('ero_3')->create();

        $this->getJson(route('api.v1.inventories.shortlist-paginate', [
            'search' => 'stoc',
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
    public function success_list_by_name_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.shortlist-paginate', [
            'search' => '555'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.shortlist-paginate'))
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
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.shortlist-paginate', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.shortlist-paginate', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
