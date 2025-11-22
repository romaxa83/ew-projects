<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders;

use App\GraphQL\Mutations\FrontOffice\Orders\OrderDisconnectProjectMutation;
use App\Models\Orders\Order;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderDisconnectProjectMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_disconnect_project(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->withProject()
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

        $this->postGraphQL($query->getMutation())
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
