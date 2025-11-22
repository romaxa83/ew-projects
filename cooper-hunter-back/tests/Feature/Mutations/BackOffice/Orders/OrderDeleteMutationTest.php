<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\GraphQL\Mutations\BackOffice\Orders\OrderDeleteMutation;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPart;
use App\Models\Orders\OrderPayment;
use App\Models\Orders\OrderShipping;
use App\Models\Orders\OrderStatusHistory;
use App\Permissions\Orders\OrderDeletePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderDeleteMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([OrderDeletePermission::KEY]);
    }

    public function test_delete_order(): void
    {
        $order = $this->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderDeleteMutation::NAME,
            [
                'id' => $order->id
            ],
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order->id,
            ]
        );

        $this->assertDatabaseMissing(
            OrderPart::class,
            [
                'order_id' => $order->id,
            ]
        );

        $this->assertDatabaseMissing(
            OrderStatusHistory::class,
            [
                'order_id' => $order->id,
            ]
        );

        $this->assertDatabaseMissing(
            OrderPayment::class,
            [
                'order_id' => $order->id,
            ]
        );

        $this->assertDatabaseMissing(
            OrderShipping::class,
            [
                'order_id' => $order->id,
            ]
        );
    }

    public function test_try_to_soft_deleted_order(): void
    {
        $order = $this->createCreatedOrder();

        $order->delete();

        $query = new GraphQLQuery(
            OrderDeleteMutation::NAME,
            [
                'id' => $order->id
            ],
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'validation'
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
            ]
        );
    }
}
