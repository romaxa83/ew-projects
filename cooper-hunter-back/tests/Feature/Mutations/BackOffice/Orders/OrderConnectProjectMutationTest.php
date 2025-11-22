<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\GraphQL\Mutations\BackOffice\Orders\OrderConnectProjectMutation;
use App\Models\Orders\Order;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderConnectProjectMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([OrderUpdatePermission::KEY]);
    }

    public function test_connect_project(): void
    {
        $order = $this->createCreatedOrder();

        $project = $this->createProjectForMember($order->technician);

        /**@var SystemUnitPivot $systemUnit */
        $systemUnit = $project->systems()
            ->first()
            ->units()
            ->first()->unit;

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

        $this->postGraphQLBackOffice($query->getMutation())
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
        $order = $this->withProject()
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

        $this->postGraphQLBackOffice($query->getMutation())
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
