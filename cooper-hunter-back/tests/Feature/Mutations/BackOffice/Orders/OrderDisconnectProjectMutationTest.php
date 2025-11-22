<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\GraphQL\Mutations\BackOffice\Orders\OrderDisconnectProjectMutation;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderDisconnectProjectMutationTest extends TestCase
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

    public function test_disconnect_project(): void
    {
        $order = $this->withProject()
            ->createCreatedOrder();

        $query = new GraphQLQuery(
            OrderDisconnectProjectMutation::NAME,
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
                        OrderDisconnectProjectMutation::NAME => [
                            'id' => $order->id,
                            'project' => null
                        ]
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Order::class,
            [
                'id' => $order->id,
                'project_id' => $order->project_id
            ]
        );
    }
}
