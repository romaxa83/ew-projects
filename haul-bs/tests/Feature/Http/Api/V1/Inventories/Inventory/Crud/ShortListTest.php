<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Crud;

use App\Http\Requests\Inventories\Inventory\InventoryShortListRequest;
use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_list_limit()
    {
        $this->loginUserAsSuperAdmin();

        for ($i = 50; $i > 0; $i--){
            Inventory::factory()->create(['name' => 'Alex', 'stock_number' => 'stock_number_'.$i]);
        }

        $this->getJson(route('api.v1.inventories.shortlist', [
            'search' => 'alex',
        ]))
            ->assertJsonCount(InventoryShortListRequest::DEFAULT_LIMIT, 'data')
        ;
    }

    /** @test */
    public function success_list_by_limit()
    {
        $this->loginUserAsSuperAdmin();

        for ($i = 50; $i > 0; $i--){
            Inventory::factory()->create(['name' => 'Alex', 'stock_number' => 'stock_number_'.$i]);
        }

        $this->getJson(route('api.v1.inventories.shortlist', [
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

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->create();

        $this->inventoryBuilder->create();
        $this->inventoryBuilder->create();

        $this->getJson(route('api.v1.inventories.shortlist', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                        'article_number' => $m_1->article_number,
                        'stock_number' => $m_1->stock_number,
                        'price' => $m_1->price_retail,
                        'quantity' => $m_1->quantity,
                        'unit' => [
                            'id' => $m_1->unit->id,
                            'name' => $m_1->unit->name,
                            'accept_decimals' => $m_1->unit->accept_decimals,
                        ],
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

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('Alex')->stock_number('stock_1')->create();
        $m_2 = $this->inventoryBuilder->name('Alex')->stock_number('stock_2')->create();
        $this->inventoryBuilder->name('Tommy')->stock_number('stock_3')->create();

        $this->getJson(route('api.v1.inventories.shortlist', [
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
    public function success_list_by_stock_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('Alex')->stock_number('stock_1')->create();
        $m_2 = $this->inventoryBuilder->name('Alex')->stock_number('stock_2')->create();
        $this->inventoryBuilder->name('Tommy')->stock_number('ero_3')->create();

        $this->getJson(route('api.v1.inventories.shortlist', [
            'search' => 'stoc',
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
    public function success_list_by_article_name()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('Alex')->article_number('stock_1')->create();
        $m_2 = $this->inventoryBuilder->name('Alex')->article_number('stock_2')->create();
        $this->inventoryBuilder->name('Tommy')->article_number('ero_3')->create();

        $this->getJson(route('api.v1.inventories.shortlist', [
            'search' => 'stoc',
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
    public function success_list_by_article_name_without_ids()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Inventory */
        $m_1 = $this->inventoryBuilder->name('Alex')->article_number('stock_1')->create();
        $m_2 = $this->inventoryBuilder->name('Alex')->article_number('stock_2')->create();
        $m_3 = $this->inventoryBuilder->name('Alex')->article_number('stock_3')->create();

        $this->getJson(route('api.v1.inventories.shortlist', [
            'search' => 'stoc',
            'without_ids' => [$m_1->id, $m_2->id],
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
    public function success_list_by_name_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.shortlist', [
            'search' => '555'
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.shortlist', [
            'search' => 'rit',
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.shortlist', [
            'search' => 'rit',
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
