<?php


namespace Tests\Feature\Queries\FrontOffice\Orders\Categories;


use App\GraphQL\Queries\FrontOffice\Orders\Categories\OrderCategoriesQuery;
use App\Models\Orders\Categories\OrderCategory;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderCategoriesQueryTest extends TestCase
{

    use DatabaseTransactions;

    private Collection $activeCategories;
    private Collection $defaultCategories;

    public function setUp(): void
    {
        parent::setUp();
        $this->defaultCategories = OrderCategory::all();
        $this->activeCategories = OrderCategory::factory()->count(2)->create();
        OrderCategory::factory(['active' => 0])->count(3)->create();
    }

    public function test_get_order_categories_list(): void
    {
        $this->loginAsTechnicianWithRole();

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [],
            [
                'id',
                'need_description'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    OrderCategoriesQuery::NAME => [
                        '*' => [
                            'id',
                            'need_description'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.' . OrderCategoriesQuery::NAME)
            ->assertJson([
                'data' => [
                    OrderCategoriesQuery::NAME => [
                        [
                            'id' => $this->activeCategories[1]->id,
                            'need_description' => false,
                        ],
                        [
                            'id' => $this->activeCategories[0]->id,
                            'need_description' => false,
                        ],
                        [
                            'id' => $this->defaultCategories[0]->id,
                            'need_description' => true,
                        ],
                    ]
                ]
            ]);
    }

    public function test_try_to_get_not_active_order_categories(): void
    {
        $this->loginAsTechnicianWithRole();

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [
                'published' => false
            ],
            [
                'id'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonStructure(['errors']);
    }

    public function test_get_active_order_categories_by_query(): void
    {
        $this->loginAsTechnicianWithRole();

        $query = new GraphQLQuery(
            OrderCategoriesQuery::NAME,
            [
                'query' => $this->activeCategories[0]->translation->description
            ],
            [
                'id'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJsonCount(1, 'data.' . OrderCategoriesQuery::NAME)
            ->assertJson([
                'data' => [
                    OrderCategoriesQuery::NAME => [
                        [
                            'id' => $this->activeCategories[0]->id
                        ]
                    ]
                ]
            ]);
    }

}
