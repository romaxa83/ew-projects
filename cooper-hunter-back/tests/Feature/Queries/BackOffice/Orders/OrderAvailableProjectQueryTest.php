<?php


namespace Feature\Queries\BackOffice\Orders;


use App\GraphQL\Queries\BackOffice\Orders\OrderAvailableProjectQuery;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Permissions\Orders\OrderListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderAvailableProjectQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([OrderListPermission::KEY]);
    }

    public function test_get_null_available_project(): void
    {
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

        $this->postGraphQLBackOffice($query->getQuery())
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
        $order = $this->withProject()
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

        $this->postGraphQLBackOffice($query->getQuery())
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
            OrderAvailableProjectQuery::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'name'
            ]
        );

        $this->postGraphQLBackOffice($query->getQuery())
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
}
