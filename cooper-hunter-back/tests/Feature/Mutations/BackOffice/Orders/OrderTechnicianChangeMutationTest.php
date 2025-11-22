<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\GraphQL\Mutations\BackOffice\Orders\OrderTechnicianChangeMutation;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderTechnicianChangeMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function test_change_technician(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createCreatedOrder();

        $technician = Technician::factory()
            ->certified()
            ->create();

        $query = new GraphQLQuery(
            OrderTechnicianChangeMutation::NAME,
            [
                'id' => $order->id,
                'technician_id' => $technician->id
            ],
            [
                'id',
                'technician' => [
                    'id'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTechnicianChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'technician' => [
                                'id' => (string)$technician->id
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_change_technician_on_order_with_project(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->withProject()
            ->createCreatedOrder();

        $technician = Technician::factory()
            ->certified()
            ->create();

        $this->assertNotNull($order->project_id);

        $query = new GraphQLQuery(
            OrderTechnicianChangeMutation::NAME,
            [
                'id' => $order->id,
                'technician_id' => $technician->id
            ],
            [
                'id',
                'project' => [
                    'id'
                ],
                'technician' => [
                    'id'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTechnicianChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'project' => null,
                            'technician' => [
                                'id' => (string)$technician->id
                            ]
                        ]
                    ]
                ]
            );

        $order->refresh();

        $this->assertNull($order->project_id);
    }

    public function test_change_technician_on_order_with_project_to_technician_with_this_project(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $technician = Technician::factory()
            ->certified()
            ->create();

        $order = $this->withProject()
            ->createCreatedOrder();

        $order->project->technicians()
            ->attach($technician->id);

        $query = new GraphQLQuery(
            OrderTechnicianChangeMutation::NAME,
            [
                'id' => $order->id,
                'technician_id' => $technician->id
            ],
            [
                'id',
                'project' => [
                    'id'
                ],
                'technician' => [
                    'id'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTechnicianChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'project' => [
                                'id' => (string)$order->project_id
                            ],
                            'technician' => [
                                'id' => (string)$technician->id
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_change_technician_on_order_with_project_to_technician_with_other_project(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $technician = Technician::factory()
            ->certified()
            ->create();

        $project = $this->createProjectForMember($technician);

        $order = $this->withProject()
            ->createCreatedOrder();

        $unit = $project->systems()
            ->first()->units->first()->unit;
        $unit->serial_number = $order->serial_number;
        $unit->save();

        $query = new GraphQLQuery(
            OrderTechnicianChangeMutation::NAME,
            [
                'id' => $order->id,
                'technician_id' => $technician->id
            ],
            [
                'id',
                'project' => [
                    'id'
                ],
                'technician' => [
                    'id'
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderTechnicianChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'project' => [
                                'id' => (string)$project->id
                            ],
                            'technician' => [
                                'id' => (string)$technician->id
                            ]
                        ]
                    ]
                ]
            );
    }
}
