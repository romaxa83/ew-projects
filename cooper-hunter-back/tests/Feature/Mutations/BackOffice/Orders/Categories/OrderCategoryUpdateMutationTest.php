<?php


namespace Feature\Mutations\BackOffice\Orders\Categories;


use App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryUpdateMutation;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Categories\OrderCategoryTranslation;
use App\Permissions\Orders\Categories\OrderCategoryUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCategoryUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use OrderCreateTrait;

    public function test_update_order_category(): void
    {
        $this->loginByAdminManager([OrderCategoryUpdatePermission::KEY]);

        /**@var OrderCategory $orderCategory*/
        $orderCategory = OrderCategory::factory()->create();

        $query = new GraphQLQuery(
            OrderCategoryUpdateMutation::NAME,
            [
                'id' => $orderCategory->id,
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

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    OrderCategoryUpdateMutation::NAME => [
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
            ->assertJsonPath('data.' . OrderCategoryUpdateMutation::NAME . '.id', (string)$orderCategory->id);

        $this->assertDatabaseHas(
            OrderCategoryTranslation::class,
            [
                'row_id' => $orderCategory->id,
                'language' => 'en'
            ]
        );

        $this->assertDatabaseHas(
            OrderCategoryTranslation::class,
            [
                'row_id' => $orderCategory->id,
                'language' => 'es'
            ]
        );
    }

    public function test_try_to_turn_off_order_category_with_orders(): void
    {
        $this->loginByAdminManager([OrderCategoryUpdatePermission::KEY]);

        $order = $this->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderCategoryUpdateMutation::NAME,
            [
                'id' => $order->parts[0]->order_category_id,
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
                'active' => false
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
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.order.order_category_used')
                        ]
                    ]
                ]
            );
    }

}
