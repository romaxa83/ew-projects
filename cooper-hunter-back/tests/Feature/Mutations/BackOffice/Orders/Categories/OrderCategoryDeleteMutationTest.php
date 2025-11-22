<?php


namespace Feature\Mutations\BackOffice\Orders\Categories;


use App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryDeleteMutation;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Categories\OrderCategoryTranslation;
use App\Permissions\Orders\Categories\OrderCategoryDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCategoryDeleteMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use OrderCreateTrait;

    public function test_delete_order_category(): void
    {
        $this->loginByAdminManager([OrderCategoryDeletePermission::KEY]);

        /**@var OrderCategory $orderCategory */
        $orderCategory = OrderCategory::factory()
            ->create();

        $query = new GraphQLQuery(
            OrderCategoryDeleteMutation::NAME,
            [
                'id' => $orderCategory->id,
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson([
                'data' => [
                    OrderCategoryDeleteMutation::NAME => true
                ]
            ]);

        $this->assertDatabaseMissing(
            OrderCategory::class,
            [
                'id' => $orderCategory->id
            ]
        );

        $this->assertDatabaseMissing(
            OrderCategoryTranslation::class,
            [
                'row_id' => $orderCategory->id
            ]
        );
    }

    public function test_try_to_delete_order_category_with_order(): void
    {
        $this->loginByAdminManager([OrderCategoryDeletePermission::KEY]);

        $order = $this->createCreatedOrder();
        $query = new GraphQLQuery(
            OrderCategoryDeleteMutation::NAME,
            [
                'id' => $order->parts[0]->order_category_id
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
