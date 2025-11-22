<?php


namespace Feature\Mutations\BackOffice\Orders\Categories;


use App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryCreateMutation;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryCreatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCategoryCreateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public function test_create_order_category(): void
    {
        $this->loginByAdminManager([OrderCategoryCreatePermission::KEY]);

        $query = new GraphQLQuery(
            OrderCategoryCreateMutation::NAME,
            [
                'translations' => [
                    [
                        'title' => 'en title',
                        'language' => 'en',
                    ],
                    [
                        'title' => 'es title',
                        'language' => 'es',
                    ]
                ],
            ],
            [
                'id',
                'translation' => [
                    'title'
                ],
                'translations' => [
                    'title'
                ]
            ]
        );

        $id = $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    OrderCategoryCreateMutation::NAME => [
                        'id',
                        'translation' => [
                            'title'
                        ],
                        'translations' => [
                            '*' => [
                                'title'
                            ]
                        ]
                    ]
                ]
            ])
            ->json('data.' . OrderCategoryCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            OrderCategory::class,
            [
                'id' => $id
            ]
        );
    }

    public function test_try_create_order_category_wo_one_language(): void
    {
        $this->loginByAdminManager([OrderCategoryCreatePermission::KEY]);

        $query = new GraphQLQuery(
            OrderCategoryCreateMutation::NAME,
            [
                'translations' => [
                    [
                        'title' => 'en title',
                        'language' => 'en',
                    ]
                ],
            ],
            [
                'id',
                'translation' => [
                    'title'
                ],
                'translations' => [
                    'title'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonPath('errors.0.message', 'validation');
    }

}
