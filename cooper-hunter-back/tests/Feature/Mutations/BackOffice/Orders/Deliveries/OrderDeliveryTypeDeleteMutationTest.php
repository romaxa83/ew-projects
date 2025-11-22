<?php


namespace Feature\Mutations\BackOffice\Orders\Deliveries;


use App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeDeleteMutation;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Orders\Deliveries\OrderDeliveryTypeTranslation;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderDeliveryTypeDeleteMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use OrderCreateTrait;

    public function test_delete_delivery_type(): void
    {
        $this->loginByAdminManager([OrderDeliveryTypeDeletePermission::KEY]);

        $orderDeliveryType = OrderDeliveryType::factory()->create();

        $query = new GraphQLQuery(
            OrderDeliveryTypeDeleteMutation::NAME,
            [
                'id' => $orderDeliveryType->id
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson([
                'data' => [
                    OrderDeliveryTypeDeleteMutation::NAME => true
                ]
            ]);

        $this->assertDatabaseMissing(
            OrderDeliveryTypeTranslation::class,
            [
                'row_id' => $orderDeliveryType->id
            ]
        );

        $this->assertDatabaseMissing(
            OrderDeliveryType::class,
            [
                'id' => $orderDeliveryType->id
            ]
        );
    }

    public function test_not_delete_type_w_orders(): void
    {
        $this->loginByAdminManager([OrderDeliveryTypeDeletePermission::KEY]);

        $technician = Technician::factory()->certified()->create();

        $order = $this->setOrderTechnician($technician)->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderDeliveryTypeDeleteMutation::NAME,
            [
                'id' => $order->shipping->order_delivery_type_id
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson([
                'errors' => [
                    [
                        'message' => trans('validation.custom.order.orders_have_this_delivery_type')
                    ]
                ]
            ]);
    }

}
