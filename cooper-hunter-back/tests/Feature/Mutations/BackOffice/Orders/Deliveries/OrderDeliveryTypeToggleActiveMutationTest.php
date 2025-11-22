<?php


namespace Feature\Mutations\BackOffice\Orders\Deliveries;


use App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeToggleActiveMutation;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderDeliveryTypeToggleActiveMutationTest extends TestCase
{

    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use OrderCreateTrait;

    public function test_off_delivery_type(): void
    {
        $this->loginByAdminManager([OrderDeliveryTypeUpdatePermission::KEY]);

        $orderDeliveryType = OrderDeliveryType::factory()->create();

        $query = new GraphQLQuery(
            OrderDeliveryTypeToggleActiveMutation::NAME,
            [
                'id' => $orderDeliveryType->id
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
                    OrderDeliveryTypeToggleActiveMutation::NAME => [
                        'id' => (string)$orderDeliveryType->id,
                        'active' => false
                    ]
                ]
            ]);

        $this->assertDatabaseHas(
            OrderDeliveryType::class,
            [
                'id' => $orderDeliveryType->id,
                'active' => 0
            ]
        );
    }

    public function test_not_delete_type_w_orders(): void
    {
        $this->loginByAdminManager([OrderDeliveryTypeUpdatePermission::KEY]);

        $technician = Technician::factory()->certified()->create();

        $order = $this->setOrderTechnician($technician)->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderDeliveryTypeToggleActiveMutation::NAME,
            [
                'id' => $order->shipping->order_delivery_type_id
            ],
            [
                'id',
                'active'
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
