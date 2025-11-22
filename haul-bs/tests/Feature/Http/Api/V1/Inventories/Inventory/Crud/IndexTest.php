<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Crud;

use App\Enums\Inventories\InventoryStockStatus;
use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Suppliers\SupplierBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected CategoryBuilder $categoryBuilder;
    protected SupplierBuilder $supplierBuilder;
    protected BrandBuilder $brandBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->supplierBuilder = resolve(SupplierBuilder::class);
        $this->brandBuilder = resolve(BrandBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('aaaaa')->quantity(11)->create();
        $m_2 = $this->inventoryBuilder->name('zzzzz')->quantity(1)->create();
        $m_3 = $this->inventoryBuilder->name('bbbbb')->quantity(0)->create();

        $this->getJson(route('api.v1.inventories'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'stock_number',
                        'article_number',
                        'price_retail',
                        'quantity',
                        'min_limit',
                        'for_shop',
                        'running_out_of_stock',
                        'status',
                        'category_name',
                        'supplier_name',
                        'brand_name',
                        'unit_name',
                        'accept_decimals',
                        'hasRelatedOpenOrders',
                        'hasRelatedDeletedOrders',
                        'hasRelatedTypesOfWork',
                        'length',
                        'width',
                        'height',
                        'weight',
                        'min_limit_price',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                    ['id' => $m_3->id],
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

        $this->inventoryBuilder->create();
        $this->inventoryBuilder->create();
        $this->inventoryBuilder->create();

        $this->getJson(route('api.v1.inventories', ['page' => 2]))
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

        $this->inventoryBuilder->create();
        $this->inventoryBuilder->create();
        $this->inventoryBuilder->create();

        $this->getJson(route('api.v1.inventories', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.inventories'))
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

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('aaaaa')->create();
        $this->inventoryBuilder->name('zzzzz')->create();
        $this->inventoryBuilder->name('bbbbb')->create();

        $this->getJson(route('api.v1.inventories', [
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
    public function success_search_by_stock_number()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $this->inventoryBuilder->stock_number('11111')->create();
        $this->inventoryBuilder->stock_number('77777')->create();
        $m_1 = $this->inventoryBuilder->stock_number('9999')->brand(null)->create();

        $this->getJson(route('api.v1.inventories', [
            'search' => '9999'
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
    public function success_search_by_name_and_category()
    {
        $this->loginUserAsSuperAdmin();

        $cat_1 = $this->categoryBuilder->create();
        $cat_2 = $this->categoryBuilder->create();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('aaaaa')->category($cat_1)->create();
        $this->inventoryBuilder->name('zzzzz')->category($cat_1)->create();
        $this->inventoryBuilder->name('aaaaa')->category($cat_2)->create();

        $this->getJson(route('api.v1.inventories', [
            'search' => 'aaaaa',
            'category_id' => $cat_1->id
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
    public function success_filter_by_category()
    {
        $this->loginUserAsSuperAdmin();

        $cat_1 = $this->categoryBuilder->create();
        $cat_2 = $this->categoryBuilder->create();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('aaaaa')->quantity(2)->category($cat_1)->create();
        $this->inventoryBuilder->quantity(0)->category($cat_2)->create();
        $m_2 = $this->inventoryBuilder->name('aaaaa')->quantity(10)->category($cat_1)->create();

        $this->getJson(route('api.v1.inventories', [
            'category_id' => $cat_1->id
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
    public function success_filter_by_supplier()
    {
        $this->loginUserAsSuperAdmin();

        $s_1 = $this->supplierBuilder->create();
        $s_2 = $this->supplierBuilder->create();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('aaaaa')->quantity(2)->supplier($s_1)->create();
        $this->inventoryBuilder->quantity(0)->supplier($s_2)->create();
        $m_2 = $this->inventoryBuilder->name('bbbb')->quantity(10)->supplier($s_1)->create();

        $this->getJson(route('api.v1.inventories', [
            'supplier_id' => $s_1->id
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
    public function success_filter_by_brand()
    {
        $this->loginUserAsSuperAdmin();

        $b_1 = $this->brandBuilder->create();
        $b_2 = $this->brandBuilder->create();

        /** @var $m_1 Inventory */
        $this->inventoryBuilder->name('aaaaa')->quantity(2)->brand($b_1)->create();
        $m_1 =  $this->inventoryBuilder->quantity(0)->brand($b_2)->create();
        $this->inventoryBuilder->name('aaaaa')->quantity(10)->brand($b_1)->create();

        $this->getJson(route('api.v1.inventories', [
            'brand_id' => $b_2->id
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
    public function success_filter_by_status()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('aaaaa')->quantity(2)->create();
        $m_2 = $this->inventoryBuilder->quantity(0)->create();
        $m_3 = $this->inventoryBuilder->name('aaaaa')->quantity(10)->create();

        $this->getJson(route('api.v1.inventories', [
            'status' => InventoryStockStatus::IN->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('api.v1.inventories', [
            'status' => InventoryStockStatus::OUT->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_filter_by_only_min_limit()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $this->inventoryBuilder->name('aaaaa')->quantity(2)->min_limit(1)->create();
        $this->inventoryBuilder->quantity(3)->min_limit(1)->create();
        $m_3 = $this->inventoryBuilder->name('aaaaa')->quantity(10)->min_limit(20)->create();

        $this->getJson(route('api.v1.inventories', [
            'only_min_limit' => true
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_filter_by_for_shop()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->for_shop(true)->create();
        $this->inventoryBuilder->for_shop(false)->create();
        $this->inventoryBuilder->for_shop(false)->create();

        $this->getJson(route('api.v1.inventories', [
            'for_shop' => true
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
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories'));

        self::assertUnauthenticatedMessage($res);
    }
}
