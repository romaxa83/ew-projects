<?php


namespace Feature\Queries\FrontOffice\Orders;


use App\GraphQL\Queries\FrontOffice\Orders\OrderTotalQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderTotalQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_get_total_order_data(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $this->setOrderTechnician($user)
            ->manyOrder(2)
            ->createAllStatusesOrder();

        $query = new GraphQLQuery(
            name: OrderTotalQuery::NAME,
            select: [
                'active',
                'history',
                'total',
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTotalQuery::NAME => [
                            'active' => 6,
                            'history' => 4,
                            'total' => 10,
                        ]
                    ]
                ]
            );
    }

    public function test_get_total_order_data_only_active(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createCreatedOrder();

        $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createPendingPaidOrder();

        $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createPaidOrder();

        $query = new GraphQLQuery(
            name: OrderTotalQuery::NAME,
            select: [
                'active',
                'history',
                'total',
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTotalQuery::NAME => [
                            'active' => 9,
                            'history' => 0,
                            'total' => 9,
                        ]
                    ]
                ]
            );
    }

    public function test_get_total_order_data_only_history(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createShippedOrder();

        $this->setOrderTechnician($user)
            ->manyOrder(3)
            ->createCanceledOrder();

        $query = new GraphQLQuery(
            name: OrderTotalQuery::NAME,
            select: [
                'active',
                'history',
                'total',
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTotalQuery::NAME => [
                            'active' => 0,
                            'history' => 6,
                            'total' => 6,
                        ]
                    ]
                ]
            );
    }

    public function test_get_total_order_without_order(): void
    {
        $this->loginAsTechnicianWithRole();

        $query = new GraphQLQuery(
            name: OrderTotalQuery::NAME,
            select: [
                'active',
                'history',
                'total',
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTotalQuery::NAME => [
                            'active' => 0,
                            'history' => 0,
                            'total' => 0,
                        ]
                    ]
                ]
            );
    }
}
