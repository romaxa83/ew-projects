<?php

namespace Tests\Feature\Mutations\BackOffice\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Mutations\FrontOffice\Orders\OrderCreateMutation;
use App\Models\Admins\Admin;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPart;
use App\Models\Orders\OrderPayment;
use App\Models\Orders\OrderShipping;
use App\Models\Orders\OrderStatusHistory;
use App\Models\Technicians\Technician;
use App\Permissions\Orders\OrderCreatePermission;
use App\ValueObjects\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCreateMutationTest extends TestCase
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

        $this->admin = $this->loginByAdminManager([OrderCreatePermission::KEY]);
    }

    public function test_create_order(): void
    {
        [$member, $project, $category, $deliveryType] = $this->getOrderCreateData();

        $state = State::first();
        $country = Country::first();

        $orderCreateData = [
            'serial_number' => $project->systems->first()->units->first()->unit->serial_number,
            'project_id' => $project->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'address_first_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,
            'comment' => $this->faker->text,
            'parts' => [
                [
                    'id' => $category->id,
                    'description' => $this->faker->title
                ]
            ]
        ];

        $query = new GraphQLQuery(
            OrderCreateMutation::NAME,
            [
                'technician_id' => $member->id,
                'order' => $orderCreateData
            ],
            [
                'id',
                'status',
                'project' => [
                    'id',
                    'name'
                ],
                'product' => [
                    'id'
                ],
                'technician' => [
                    'id',
                    'first_name',
                    'last_name'
                ],
                'serial_number',
                'first_name',
                'last_name',
                'phone',
                'comment',
                'parts' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
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
                        'sort',
                        'active',
                        'translation' => [
                            'id',
                            'title',
                            'slug',
                            'description',
                            'language'
                        ],
                        'translations' => [
                            'id',
                            'title',
                            'slug',
                            'description',
                            'language'
                        ]
                    ],
                    'trk_number' => [
                        'number',
                        'tracking_url'
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
                        OrderCreateMutation::NAME => [
                            'status' => OrderStatusEnum::CREATED,
                            'project' => [
                                'id' => $project->id,
                                'name' => $project->name
                            ],
                            'product' => [
                                'id' => $project->systems->first()->units->first()->id,
                            ],
                            'technician' => [
                                'id' => (string)$member->id,
                                'first_name' => $member->first_name,
                                'last_name' => $member->last_name,
                            ],
                            'serial_number' => $orderCreateData['serial_number'],
                            'first_name' => $orderCreateData['first_name'],
                            'last_name' => $orderCreateData['last_name'],
                            'phone' => new Phone($orderCreateData['phone']),
                            'comment' => $orderCreateData['comment'],
                            'parts' => [
                                [
                                    'id' => (string)$category->id,
                                    'name' => $category->translation->title,
                                    'description' => $orderCreateData['parts'][0]['description'],
                                    'quantity' => config('orders.categories.default_quantity'),
                                    'price' => null
                                ],
                            ],
                            'shipping' => [
                                'first_name' => $orderCreateData['first_name'],
                                'last_name' => $orderCreateData['last_name'],
                                'phone' => new Phone($orderCreateData['phone']),
                                'address_first_line' => $orderCreateData['address_first_line'],
                                'address_second_line' => null,
                                'city' => $orderCreateData['city'],
                                'country' => [
                                    'id' => $country->id
                                ],
                                'state' => [
                                    'id' => $state->id
                                ],
                                'zip' => $orderCreateData['zip'],
                                'deliveryType' => [
                                    'sort' => $deliveryType->sort,
                                    'active' => $deliveryType->active,
                                    'translation' => [
                                        'id' => $deliveryType->translation->id,
                                        'title' => $deliveryType->translation->title,
                                        'slug' => $deliveryType->translation->slug,
                                        'description' => $deliveryType->translation->description,
                                        'language' => $deliveryType->translation->language,
                                    ],
                                    'translations' => [
                                        [
                                            'id' => $deliveryType->translations[0]->id,
                                            'title' => $deliveryType->translations[0]->title,
                                            'slug' => $deliveryType->translations[0]->slug,
                                            'description' => $deliveryType->translations[0]->description,
                                            'language' => $deliveryType->translations[0]->language,
                                        ],
                                        [
                                            'id' => $deliveryType->translations[1]->id,
                                            'title' => $deliveryType->translations[1]->title,
                                            'slug' => $deliveryType->translations[1]->slug,
                                            'description' => $deliveryType->translations[1]->description,
                                            'language' => $deliveryType->translations[1]->language,
                                        ],
                                    ]
                                ],
                                'trk_number' => null
                            ],
                            'payment' => [
                                'cost_status' => OrderCostStatusEnum::NOT_FORMED,
                                'order_price' => null,
                                'order_price_with_discount' => null,
                                'shipping_cost' => null,
                                'tax' => null,
                                'discount' => null,
                                'paid_at' => null
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . OrderCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $orderId,
                'status' => OrderStatusEnum::CREATED,
                'changer_id' => $this->admin->id
            ]
        );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $orderId,
                'status' => OrderStatusEnum::CREATED
            ]
        );

        $this->assertDatabaseCount(
            OrderPart::class,
            1
        );

        $this->assertDatabaseHas(
            OrderShipping::class,
            [
                'order_id' => $orderId
            ]
        );

        $this->assertDatabaseHas(
            OrderPayment::class,
            [
                'order_id' => $orderId
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

    public function test_try_create_order_with_payment_and_without_part_price(): void
    {
        [$member, $project, $category, $deliveryType] = $this->getOrderCreateData();

        $state = State::first();
        $country = Country::first();

        $orderCreateData = [
            'serial_number' => $project->systems->first()->units->first()->unit->serial_number,
            'project_id' => $project->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'address_first_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,
            'comment' => $this->faker->text,
            'parts' => [
                [
                    'id' => $category->id,
                    'description' => $this->faker->title
                ]
            ],
            'payment' => [
                'order_price' => $this->faker->randomFloat(),
                'order_price_with_discount' => $this->faker->randomFloat(),
                'shipping_cost' => 0.0,
                'tax' => 0.0,
                'discount' => 0.0
            ]
        ];

        $query = new GraphQLQuery(
            OrderCreateMutation::NAME,
            [
                'technician_id' => $member->id,
                'order' => $orderCreateData
            ],
            [
                'id',
                'status',
                'parts' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
                    'price'
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

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.order.order_part_price_is_required')
                        ]
                    ]
                ]
            );
    }

    public function test_auto_change_status_pending_paid_order(): void
    {
        [$member, $project, $category, $deliveryType] = $this->getOrderCreateData();

        $price = round(random_int(100,1000), 2);

        $state = State::first();
        $country = Country::first();

        $orderCreateData = [
            'status' => new EnumValue(OrderStatusEnum::CREATED),
            'serial_number' => $project->systems->first()->units->first()->unit->serial_number,
            'project_id' => $project->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'address_first_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,
            'comment' => $this->faker->text,
            'parts' => [
                [
                    'id' => $category->id,
                    'description' => $this->faker->title,
                    'price' => $price
                ]
            ],
            'payment' => [
                'order_price' => $price,
                'order_price_with_discount' => $price,
                'shipping_cost' => 0.0,
                'tax' => 0.0,
                'discount' => 0.0
            ]
        ];

        $query = new GraphQLQuery(
            OrderCreateMutation::NAME,
            [
                'technician_id' => $member->id,
                'order' => $orderCreateData
            ],
            [
                'id',
                'status',
                'parts' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
                    'price'
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
                        OrderCreateMutation::NAME => [
                            'status' => OrderStatusEnum::PENDING_PAID,
                            'parts' => [
                                [
                                    'id' => (string)$category->id,
                                    'name' => $category->translation->title,
                                    'description' => $orderCreateData['parts'][0]['description'],
                                    'quantity' => config('orders.categories.default_quantity'),
                                    'price' => $price
                                ],
                            ],
                            'payment' => [
                                'cost_status' => OrderCostStatusEnum::WAITING_TO_PAY,
                                'order_price' => $price,
                                'order_price_with_discount' => $price,
                                'shipping_cost' => 0.0,
                                'tax' => 0.0,
                                'discount' => 0.0,
                                'paid_at' => null
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . OrderCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $orderId,
                'status' => OrderStatusEnum::CREATED,
                'changer_id' => $this->admin->id
            ]
        );

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $orderId,
                'status' => OrderStatusEnum::PENDING_PAID,
                'changer_id' => $this->admin->id
            ]
        );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $orderId,
                'status' => OrderStatusEnum::PENDING_PAID,
                'technician_id' => $member->id
            ]
        );

        $price *= 100;

        $this->assertDatabaseHas(
            OrderPart::class,
            [
                'order_id' => $orderId,
                'order_category_id' => $category->id,
                'price' => $price
            ]
        );

        $this->assertDatabaseHas(
            OrderShipping::class,
            [
                'order_id' => $orderId
            ]
        );

        $this->assertDatabaseHas(
            OrderPayment::class,
            [
                'order_id' => $orderId,
                'order_price' => $price
            ]
        );
    }

    public function test_auto_change_status_paid_order(): void
    {
        [$member, $project, $category, $deliveryType] = $this->getOrderCreateData();

        $price = round(random_int(100,1000), 2);

        $paidAt = time();

        $state = State::first();
        $country = Country::first();

        $orderCreateData = [
            'status' => new EnumValue(OrderStatusEnum::CREATED),
            'serial_number' => $project->systems->first()->units->first()->unit->serial_number,
            'project_id' => $project->id,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'address_first_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,
            'comment' => $this->faker->text,
            'parts' => [
                [
                    'id' => $category->id,
                    'description' => $this->faker->title,
                    'price' => $price
                ]
            ],
            'payment' => [
                'order_price' => $price,
                'order_price_with_discount' => $price,
                'shipping_cost' => 0.0,
                'tax' => 0.0,
                'discount' => 0.0,
                'paid_at' => $paidAt,
            ]
        ];

        $query = new GraphQLQuery(
            OrderCreateMutation::NAME,
            [
                'technician_id' => $member->id,
                'order' => $orderCreateData
            ],
            [
                'id',
                'status',
                'parts' => [
                    'id',
                    'name',
                    'description',
                    'quantity',
                    'price'
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
                        OrderCreateMutation::NAME => [
                            'status' => OrderStatusEnum::PAID,
                            'parts' => [
                                [
                                    'id' => (string)$category->id,
                                    'name' => $category->translation->title,
                                    'description' => $orderCreateData['parts'][0]['description'],
                                    'quantity' => config('orders.categories.default_quantity'),
                                    'price' => $price
                                ],
                            ],
                            'payment' => [
                                'cost_status' => OrderCostStatusEnum::PAID,
                                'order_price' => $price,
                                'order_price_with_discount' => $price,
                                'shipping_cost' => 0.0,
                                'tax' => 0.0,
                                'discount' => 0.0,
                                'paid_at' => $paidAt
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . OrderCreateMutation::NAME . '.id');

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $orderId,
                'status' => OrderStatusEnum::CREATED,
                'changer_id' => $this->admin->id
            ]
        );

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $orderId,
                'status' => OrderStatusEnum::PAID,
                'changer_id' => $this->admin->id
            ]
        );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $orderId,
                'status' => OrderStatusEnum::PAID,
                'technician_id' => $member->id
            ]
        );
    }
}
