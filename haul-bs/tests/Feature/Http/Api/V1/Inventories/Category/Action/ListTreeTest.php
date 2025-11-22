<?php

namespace Feature\Http\Api\V1\Inventories\Category\Action;

use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class ListTreeTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Category */
        $root_1 = $this->categoryBuilder->name('root_1')->position(1)->create();
        $root_2 = $this->categoryBuilder->name('root_2')->position(2)->create();

        $cat_1_1 = $this->categoryBuilder->name('cat_1_1')->parent($root_1)->position(1)->create();
        $cat_1_2 = $this->categoryBuilder->name('cat_1_2')->parent($root_1)->position(3)->create();
        $cat_1_3 = $this->categoryBuilder->name('cat_1_3')->parent($root_1)->position(2)->create();

        $this->inventoryBuilder->category($cat_1_3)->create();

        $cat_1_1_1 = $this->categoryBuilder->name('cat_1_1_1')->parent($cat_1_1)->position(1)->create();
        $cat_1_1_2 = $this->categoryBuilder->name('cat_1_1_2')->parent($cat_1_1)->position(2)->create();

        $cat_1_2_1 = $this->categoryBuilder->name('cat_1_2_1')->parent($cat_1_2)->position(3)->create();
        $cat_1_2_2 = $this->categoryBuilder->name('cat_1_2_2')->parent($cat_1_2)->position(2)->create();
        $cat_1_2_3 = $this->categoryBuilder->name('cat_1_2_3')->parent($cat_1_2)->position(1)->create();

        $cat_2_1 = $this->categoryBuilder->name('cat_2_1')->parent($root_2)->position(1)->create();
        $cat_2_2 = $this->categoryBuilder->name('cat_2_2')->parent($root_2)->position(2)->create();

        $this->getJson(route('api.v1.inventories.category.list-tree'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'position',
                        'hasRelatedEntities',
                        'hasChildrenRelatedEntities',
                        'children' => [
                            [
                                'id',
                                'name',
                                'slug',
                                'position',
                                'hasRelatedEntities',
                                'children' => []
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    [
                        'id' => $root_1->id,
                        'children' => [
                            [
                                'id' => $cat_1_1->id,
                                'hasRelatedEntities' => false,
                                'children' => [
                                    ['id' => $cat_1_1_1->id],
                                    ['id' => $cat_1_1_2->id],
                                ]
                            ],
                            [
                                'id' => $cat_1_3->id,
                                'hasRelatedEntities' => true,
                                'children' => null
                            ],
                            [
                                'id' => $cat_1_2->id,
                                'hasRelatedEntities' => false,
                                'children' => [
                                    ['id' => $cat_1_2_3->id],
                                    ['id' => $cat_1_2_2->id],
                                    ['id' => $cat_1_2_1->id],
                                ]
                            ],
                        ]
                    ],
                    [
                        'id' => $root_2->id,
                        'children' => [
                            [
                                'id' => $cat_2_1->id,
                                'children' => null
                            ],
                            [
                                'id' => $cat_2_2->id,
                                'children' => null
                            ]
                        ]
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function success_list_search()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Category */
        $root_1 = $this->categoryBuilder->name('root_1')->position(1)->create();
        $root_2 = $this->categoryBuilder->name('root_2')->position(2)->create();

        $cat_1_1 = $this->categoryBuilder->name('cat_1_1')->parent($root_1)->position(1)->create();
        $cat_1_2 = $this->categoryBuilder->name('cat_1_2')->parent($root_1)->position(3)->create();
        $cat_1_3 = $this->categoryBuilder->name('cat_1_3')->parent($root_1)->position(2)->create();

        $this->inventoryBuilder->category($cat_1_3)->create();

        $cat_1_1_1 = $this->categoryBuilder->name('cat_1_1_1')->parent($cat_1_1)->position(1)->create();
        $cat_1_1_2 = $this->categoryBuilder->name('cat_1_1_2')->parent($cat_1_1)->position(2)->create();

        $cat_1_2_1 = $this->categoryBuilder->name('cat_1_2_1')->parent($cat_1_2)->position(3)->create();
        $cat_1_2_2 = $this->categoryBuilder->name('cat_1_2_2')->parent($cat_1_2)->position(2)->create();
        $cat_1_2_3 = $this->categoryBuilder->name('cat_1_2_3')->parent($cat_1_2)->position(1)->create();

        $cat_2_1 = $this->categoryBuilder->name('cat_2_1')->parent($root_2)->position(1)->create();
        $cat_2_2 = $this->categoryBuilder->name('cat_2_2')->parent($root_2)->position(2)->create();

        $this->getJson(route('api.v1.inventories.category.list-tree', [
            'search' => 'cat_2_'
        ]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'position',
                        'hasRelatedEntities',
                        'hasChildrenRelatedEntities',
                        'parent_id',
                        'display_menu',
                        'header_image',
                        'menu_image',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $cat_2_1->id],
                    ['id' => $cat_2_2->id],
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_has_children_relation_entities()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $root_1 Category */
        $root_1 = $this->categoryBuilder->name('root_1')->position(1)->create();

        // has children entities
        $cat_1_1 = $this->categoryBuilder->name('cat_1_1')->parent($root_1)->position(1)->create();
        // not has children entities
        $cat_1_2 = $this->categoryBuilder->name('cat_1_2')->parent($root_1)->position(3)->create();
        $cat_1_3 = $this->categoryBuilder->name('cat_1_3')->parent($root_1)->position(2)->create();


        $cat_1_1_1 = $this->categoryBuilder->name('cat_1_1_1')->parent($cat_1_1)->position(1)->create();
        $cat_1_1_2 = $this->categoryBuilder->name('cat_1_1_2')->parent($cat_1_1)->position(2)->create();

        $this->inventoryBuilder->category($cat_1_1_1)->create();

        $cat_1_2_1 = $this->categoryBuilder->name('cat_1_2_1')->parent($cat_1_2)->position(3)->create();
        $cat_1_2_2 = $this->categoryBuilder->name('cat_1_2_2')->parent($cat_1_2)->position(2)->create();

        $cat_1_3_1 = $this->categoryBuilder->name('cat_1_3_1')->parent($cat_1_3)->position(1)->create();

        $this->getJson(route('api.v1.inventories.category.list-tree'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $root_1->id,
                        'children' => [
                            [
                                'id' => $cat_1_1->id,
                                'hasChildrenRelatedEntities' => true,
                                'children' => [
                                    [
                                        'id' => $cat_1_1_1->id,
                                        'hasChildrenRelatedEntities' => false,
                                    ],
                                    [
                                        'id' => $cat_1_1_2->id,
                                        'hasChildrenRelatedEntities' => false,
                                    ]
                                ]
                            ],
                            [
                                'id' => $cat_1_3->id,
                                'hasChildrenRelatedEntities' => false,
                                'children' => [
                                    [
                                        'id' => $cat_1_3_1->id
                                    ]
                                ]
                            ],
                            [
                                'id' => $cat_1_2->id,
                                'hasChildrenRelatedEntities' => false,
                                'children' => [
                                    [
                                        'id' => $cat_1_2_2->id
                                    ],
                                    [
                                        'id' => $cat_1_2_1->id
                                    ],
                                ]
                            ]
                        ]
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.category.list-tree'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.category.list-tree'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.category.list-tree'));

        self::assertUnauthenticatedMessage($res);
    }
}
