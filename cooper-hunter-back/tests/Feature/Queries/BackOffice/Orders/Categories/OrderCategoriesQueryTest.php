<?php


namespace Tests\Feature\Queries\BackOffice\Orders\Categories;


use App\GraphQL\Queries\BackOffice\Orders\Categories\OrderCategoriesQuery;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCategoriesQueryTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    private Collection $activeCategories;
    private Collection $notActiveCategories;

    public function setUp(): void
    {
        parent::setUp();
        $this->activeCategories = OrderCategory::factory()->count(2)->create();
        $this->notActiveCategories = OrderCategory::factory(['active' => 0])->count(3)->create();
    }

    public function test_get_order_categories_list(): void
    {
        $this->loginByAdminManager([OrderCategoryListPermission::KEY]);

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [],
            [
                'id',
                'translation' => [
                    'title'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    OrderCategoriesQuery::NAME => [
                        '*' => [
                            'id',
                            'translation' => [
                                'title'
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(5, 'data.' . OrderCategoriesQuery::NAME);
    }

    public function test_get_active_order_categories_list(): void
    {
        $this->loginByAdminManager([OrderCategoryListPermission::KEY]);

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [
                'published' => true
            ],
            [
                'id'
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(2, 'data.' . OrderCategoriesQuery::NAME)
            ->assertJson([
                'data' => [
                    OrderCategoriesQuery::NAME => [
                        [
                            'id' => $this->activeCategories[1]->id
                        ],
                        [
                            'id' => $this->activeCategories[0]->id
                        ],
                    ]
                ]
            ]);
    }

    public function test_get_not_active_order_categories_list(): void
    {
        $this->loginByAdminManager([OrderCategoryListPermission::KEY]);

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [
                'published' => false
            ],
            [
                'id'
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(3, 'data.' . OrderCategoriesQuery::NAME)
            ->assertJson([
                'data' => [
                    OrderCategoriesQuery::NAME => [
                        [
                            'id' => $this->notActiveCategories[2]->id
                        ],
                        [
                            'id' => $this->notActiveCategories[1]->id
                        ],
                        [
                            'id' => $this->notActiveCategories[0]->id
                        ],
                    ]
                ]
            ]);
    }

    public function test_get_order_categories_list_by_query(): void
    {
        $this->loginByAdminManager([OrderCategoryListPermission::KEY]);

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [
                'query' => $this->activeCategories[0]->translation->description
            ],
            [
                'id'
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(1, 'data.' . OrderCategoriesQuery::NAME)
            ->assertJson(
                [
                    'data' => [
                        OrderCategoriesQuery::NAME => [
                            [
                                'id' => $this->activeCategories[0]->id
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_order_categories_list_with_default(): void
    {
        $this->loginByAdminManager([OrderCategoryListPermission::KEY]);

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [
                'for_edit' => false
            ],
            [
                'id',
                'need_description'
            ]
        );

        $defaultCategory = OrderCategory::whereNeedDescription(true)
            ->first();

        $this->postGraphQLBackOffice($query->getQuery())
            ->assertOk()
            ->assertJsonCount(3, 'data.' . OrderCategoriesQuery::NAME)
            ->assertJson(
                [
                    'data' => [
                        OrderCategoriesQuery::NAME => [
                            [
                                'id' => $this->activeCategories[1]->id,
                                'need_description' => false
                            ],
                            [
                                'id' => $this->activeCategories[0]->id,
                                'need_description' => false
                            ],
                            [
                                'id' => $defaultCategory->id,
                                'need_description' => true
                            ]
                        ]
                    ]
                ]
            );
    }

}
