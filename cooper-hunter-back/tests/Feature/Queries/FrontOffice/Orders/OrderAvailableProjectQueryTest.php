<?php


namespace Feature\Queries\FrontOffice\Orders;


use App\GraphQL\Queries\FrontOffice\Orders\OrderAvailableProjectQuery;
use App\Models\Projects\Pivot\SystemUnitPivot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderAvailableProjectQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_get_null_available_project(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($user)
                       ->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderAvailableProjectQuery::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'name'
            ]
        );

        $this->postGraphQL($query->getQuery())
             ->assertOk()
             ->assertJson(
                 [
                     'data' => [
                         OrderAvailableProjectQuery::NAME => null
                     ]
                 ]
             );
    }

    public function test_get_available_project_w_connected_project(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($user)
                      ->withProject()
                      ->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderAvailableProjectQuery::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'name'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderAvailableProjectQuery::NAME => null
                    ]
                ]
            );
    }

    public function test_get_available_project(): void
    {
        $user = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($user)
                      ->createCreatedOrder();

        $project = $this->createProjectForMember($user);

        /**@var SystemUnitPivot $systemUnit*/
        $systemUnit = $project->systems()->first()->units()->first()->unit;

        $order->product_id = $systemUnit->product_id;
        $order->serial_number = $systemUnit->serial_number;
        $order->save();

        $query = new GraphQLQuery(
            OrderAvailableProjectQuery::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'name'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderAvailableProjectQuery::NAME => [
                            'id' => $project->id,
                            'name' => $project->name
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_get_data_by_alien_order(): void
    {
        $this->loginAsTechnicianWithRole();

        $order = $this->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderAvailableProjectQuery::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'name'
            ]
        );

        $this->postGraphQL($query->getQuery())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.order.orders_not_found')
                        ]
                    ]
                ]
            );
    }
}
