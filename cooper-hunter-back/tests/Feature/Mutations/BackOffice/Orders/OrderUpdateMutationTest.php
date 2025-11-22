<?php

namespace Tests\Feature\Mutations\BackOffice\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Mutations\BackOffice\Orders\OrderUpdateMutation;
use App\Models\Admins\Admin;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Orders\Order;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderUpdatePermission;
use App\ValueObjects\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    private Admin $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->loginByAdminManager([OrderUpdatePermission::KEY]);
    }

    public function test_update_order(): void
    {
        [$member, $project, $category, $deliveryType] = $this->getOrderCreateData();

        $order = $this->createCreatedOrder();

        $state = State::query()->where('id', '!=', $order->shipping->state_id)->first();
        $country = Country::query()->where('id', '!=', $order->shipping->country_id)->first();

        $orderUpdateData = [
            'status' => new EnumValue(OrderStatusEnum::PENDING_PAID),
            'serial_number' => $project->systems->first()->units->first()->unit->serial_number,
            'project_id' => $project->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'address_first_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state_id' => $state->id,
            'country_code' => $country->country_code,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,
            'comment' => $this->faker->text,
            'parts' => [
                [
                    'id' => $category->id,
                    'description' => $this->faker->title,
                    'price' => 100.00
                ]
            ],
            'payment' => [
                'order_price' => 100.00,
                'order_price_with_discount' => 100.00,
                'shipping_cost' => 0.0,
                'tax' => 0.0,
                'discount' => 0.0,
            ]
        ];

        $query = new GraphQLQuery(
            OrderUpdateMutation::NAME,
            [
                'id' => $order->id,
                'technician_id' => $member->id,
                'order' => $orderUpdateData
            ],
            [
                'id',
                'status',
                'project' => [
                    'id'
                ],
                'product' => [
                    'id'
                ],
                'technician' => [
                    'id'
                ],
                'serial_number',
                'first_name',
                'last_name',
                'phone',
                'comment',
                'parts' => [
                    'id',
                    'price'
                ],
                'shipping' => [
                    'first_name',
                    'last_name',
                    'phone',
                    'address_first_line',
                    'address_second_line',
                    'city',
                    'country' => [
                        'id'
                    ],
                    'state' => [
                        'id'
                    ],
                    'zip',
                    'deliveryType' => [
                        'id',
                    ]
                ],
                'payment' => [
                    'cost_status',
                    'order_price',
                    'order_price_with_discount',
                    'shipping_cost',
                    'tax',
                    'discount',
                    'paid_at'
                ]
            ]
        );

        $orderId = $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderUpdateMutation::NAME => [
                            'status' => OrderStatusEnum::PENDING_PAID,
                            'project' => [
                                'id' => (string)$project->id
                            ],
                            'product' => [
                                'id' => (string)$project->systems->first()->units->first()->id,
                            ],
                            'technician' => [
                                'id' => (string)$member->id
                            ],
                            'serial_number' => $orderUpdateData['serial_number'],
                            'first_name' => $orderUpdateData['first_name'],
                            'last_name' => $orderUpdateData['last_name'],
                            'phone' => new Phone($orderUpdateData['phone']),
                            'comment' => $orderUpdateData['comment'],
                            'parts' => [
                                [
                                    'id' => (string)$category->id,
                                    'price' => 100
                                ],
                            ],
                            'shipping' => [
                                'first_name' => $orderUpdateData['first_name'],
                                'last_name' => $orderUpdateData['last_name'],
                                'phone' => new Phone($orderUpdateData['phone']),
                                'address_first_line' => $orderUpdateData['address_first_line'],
                                'address_second_line' => null,
                                'city' => $orderUpdateData['city'],
                                'country' => [
                                    'id' => $country->id
                                ],
                                'state' => [
                                    'id' => $state->id
                                ],
                                'zip' => $orderUpdateData['zip'],
                                'deliveryType' => [
                                    'id' => $deliveryType->id,
                                ]
                            ],
                            'payment' => [
                                'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                'order_price' => 100,
                                'order_price_with_discount' => 100,
                                'shipping_cost' => 0,
                                'tax' => 0,
                                'discount' => 0,
                                'paid_at' => null,
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . OrderUpdateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $orderId,
                'status' => OrderStatusEnum::PENDING_PAID,
                'technician_id' => $member->id,
                'project_id' => $project->id
            ]
        );
    }

    private function getOrderCreateData(): array
    {
        $member = Technician::factory()
            ->certified()
            ->create();

        $project = $this->createProjectForMember($member);

        $category = OrderCategory::query()
            ->first();

        /**@var OrderDeliveryType $deliveryType */
        $deliveryType = OrderDeliveryType::query()
            ->first();

        return [$member, $project, $category, $deliveryType];
    }
}
