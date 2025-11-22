<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\GraphQL\Mutations\BackOffice\Orders\OrderSerialNumberChangeMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Orders\Order;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderSerialNumberChangeMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function test_change_serial_number(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createCreatedOrder();

        /**@var Product $product */
        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()
                    ->state(
                        ['serial_number' => $this->faker->lexify]
                    ),
                'serialNumbers'
            )
            ->create();

        $serialNumber = $product->serialNumbers->first()->serial_number;

        $query = new GraphQLQuery(
            OrderSerialNumberChangeMutation::NAME,
            [
                'id' => $order->id,
                'serial_number' => $serialNumber
            ],
            [
                'id',
                'product' => [
                    'id'
                ],
                'serial_number'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderSerialNumberChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'product' => [
                                'id' => (string)$product->id
                            ],
                            'serial_number' => $serialNumber
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'serial_number' => $serialNumber,
                'product_id' => $product->id
            ]
        );
    }

    public function test_change_serial_number_on_order_with_project(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->withProject()
            ->createCreatedOrder();

        $this->assertNotNull($order->project_id);

        /**@var Product $product */
        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()
                    ->state(
                        ['serial_number' => $this->faker->lexify]
                    ),
                'serialNumbers'
            )
            ->create();

        $serialNumber = $product->serialNumbers->first()->serial_number;

        $query = new GraphQLQuery(
            OrderSerialNumberChangeMutation::NAME,
            [
                'id' => $order->id,
                'serial_number' => $serialNumber
            ],
            [
                'id',
                'project' => [
                    'id'
                ],
                'product' => [
                    'id'
                ],
                'serial_number'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderSerialNumberChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'project' => null,
                            'product' => [
                                'id' => (string)$product->id
                            ],
                            'serial_number' => $serialNumber
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'serial_number' => $serialNumber,
                'product_id' => $product->id,
                'project_id' => null,
            ]
        );
    }

    public function test_change_serial_number_on_order_with_project_to_number_with_other_project(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->withProject()
            ->createCreatedOrder();

        $this->assertNotNull($order->project_id);

        $project = $this->createProjectForMember($order->technician);

        $unit = $project->systems()
            ->first()
            ->units()
            ->first()->unit;

        $query = new GraphQLQuery(
            OrderSerialNumberChangeMutation::NAME,
            [
                'id' => $order->id,
                'serial_number' => $unit->serial_number
            ],
            [
                'id',
                'project' => [
                    'id'
                ],
                'product' => [
                    'id'
                ],
                'serial_number'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderSerialNumberChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'project' => [
                                'id' => $project->id,
                            ],
                            'product' => [
                                'id' => (string)$unit->product_id
                            ],
                            'serial_number' => $unit->serial_number
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'serial_number' => $unit->serial_number,
                'product_id' => $unit->product_id,
                'project_id' => $project->id,
            ]
        );
    }

    public function test_change_serial_number_on_order_wo_project_to_number_with_other_project(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createCreatedOrder();

        $this->assertNull($order->project_id);

        $project = $this->createProjectForMember($order->technician);

        $unit = $project->systems()
            ->first()
            ->units()
            ->first()->unit;

        $query = new GraphQLQuery(
            OrderSerialNumberChangeMutation::NAME,
            [
                'id' => $order->id,
                'serial_number' => $unit->serial_number
            ],
            [
                'id',
                'project' => [
                    'id'
                ],
                'product' => [
                    'id'
                ],
                'serial_number'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderSerialNumberChangeMutation::NAME => [
                            'id' => (string)$order->id,
                            'project' => null,
                            'product' => [
                                'id' => (string)$unit->product_id
                            ],
                            'serial_number' => $unit->serial_number
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $order->id,
                'serial_number' => $unit->serial_number,
                'product_id' => $unit->product_id,
                'project_id' => null,
            ]
        );
    }
}
