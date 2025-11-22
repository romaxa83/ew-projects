<?php


namespace Feature\Mutations\BackOffice\Orders\Categories;


use App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryToggleActiveMutation;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCategoryToggleActiveMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use OrderCreateTrait;

    public function test_toggle_active_off_order_category(): void
    {
        $this->loginByAdminManager([OrderCategoryUpdatePermission::KEY]);

        /**@var OrderCategory $orderCategory*/
        $orderCategory = OrderCategory::factory()->create();

        $this->assertEquals(1, $orderCategory->active);

        $query = new GraphQLQuery(
            OrderCategoryToggleActiveMutation::NAME,
            [
                'id' => $orderCategory->id,
            ],
            [
                'id',
                'active'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson([
                'data' => [
                    OrderCategoryToggleActiveMutation::NAME => [
                        'id' => (string)$orderCategory->id,
                        'active' => false
                    ]
                ]
            ], true);

        $this->assertDatabaseHas(
            OrderCategory::class,
            [
                'id' => $orderCategory->id,
                'active' => 0
            ]
        );
    }

    public function test_toggle_active_on_order_category(): void
    {
        $this->loginByAdminManager([OrderCategoryUpdatePermission::KEY]);

        /**@var OrderCategory $orderCategory*/
        $orderCategory = OrderCategory::factory(['active' => 0])->create();

        $this->assertEquals(0, $orderCategory->active);

        $query = new GraphQLQuery(
            OrderCategoryToggleActiveMutation::NAME,
            [
                'id' => $orderCategory->id,
            ],
            [
                'id',
                'active'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson([
                'data' => [
                    OrderCategoryToggleActiveMutation::NAME => [
                        'id' => (string)$orderCategory->id,
                        'active' => true
                    ]
                ]
            ], true);

        $this->assertDatabaseHas(
            OrderCategory::class,
            [
                'id' => $orderCategory->id,
                'active' => 1
            ]
        );
    }

    public function test_try_turn_off_order_category_with_order(): void
    {
        $this->loginByAdminManager([OrderCategoryUpdatePermission::KEY]);

        $order = $this->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderCategoryToggleActiveMutation::NAME,
            [
                'id' => $order->parts[0]->order_category_id,
            ],
            [
                'id',
                'active'
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
                ],
                true
            );
    }

}
