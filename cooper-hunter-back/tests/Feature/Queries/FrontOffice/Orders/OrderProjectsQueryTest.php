<?php


namespace Feature\Queries\FrontOffice\Orders;


use App\Enums\Orders\OrderFilterTabEnum;
use App\GraphQL\Queries\FrontOffice\Orders\OrderProjectsQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderProjectsQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_get_order_projects(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($user)
            ->withProject()
            ->createCreatedOrder();

        $query = new GraphQLQuery(
            name: OrderProjectsQuery::NAME,
            select: [
                'id',
                'name'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderProjectsQuery::NAME => [
                            [
                                'id' => $order->project_id,
                                'name' => $order->project->name
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_sort_projects(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $orderCreate = $this->setOrderTechnician($user)
            ->withProject()
            ->createCreatedOrder();

        $orderPendingPaid = $this->setOrderTechnician($user)
            ->withProject()
            ->manyOrder(2)
            ->createPaidOrder();

        $query = new GraphQLQuery(
            name: OrderProjectsQuery::NAME,
            select: [
                'id',
                'name'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderProjectsQuery::NAME => [
                            [
                                'id' => $orderPendingPaid[0]->project_id,
                                'name' => $orderPendingPaid[0]->project->name
                            ],
                            [
                                'id' => $orderCreate->project_id,
                                'name' => $orderCreate->project->name
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_get_project_by_tab(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $this->setOrderTechnician($user)
            ->withProject()
            ->createCreatedOrder();

        $orderShipped = $this->setOrderTechnician($user)
            ->withProject()
            ->manyOrder(2)
            ->createShippedOrder();

        $query = new GraphQLQuery(
            name: OrderProjectsQuery::NAME,
            args: [
                'tab' => new EnumValue(OrderFilterTabEnum::HISTORY)
            ],
            select: [
                'id',
                'name'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderProjectsQuery::NAME => [
                            [
                                'id' => $orderShipped[0]->project_id,
                                'name' => $orderShipped[0]->project->name
                            ]
                        ]
                    ]
                ]
            );
    }
}
