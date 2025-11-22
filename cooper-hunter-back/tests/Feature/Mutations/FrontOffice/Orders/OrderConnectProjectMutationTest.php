<?php


namespace Feature\Mutations\FrontOffice\Orders;


use App\GraphQL\Mutations\FrontOffice\Orders\OrderConnectProjectMutation;
use App\Models\Orders\Order;
use App\Models\Projects\Pivot\SystemUnitPivot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderConnectProjectMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_connect_project(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createCreatedOrder();

        $project = $this->createProjectForMember($member);

        /**@var SystemUnitPivot $systemUnit*/
        $systemUnit = $project->systems()->first()->units()->first()->unit;

        $order->product_id = $systemUnit->product_id;
        $order->serial_number = $systemUnit->serial_number;
        $order->save();

        $query = new GraphQLQuery(
            OrderConnectProjectMutation::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'project' => [
                    'id',
                    'name'
                ]
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderConnectProjectMutation::NAME => [
                            'id' => $order->id,
                            'project' => [
                                'id' => $project->id,
                                'name' => $project->name
                            ]
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'project_id' => $project->id
            ]
        );
    }

    public function test_try_to_connect_project_to_order_with_project(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->withProject()
            ->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderConnectProjectMutation::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'project' => [
                    'id',
                    'name'
                ]
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.project.forbidden')
                        ]
                    ]
                ]
            );
    }
}
