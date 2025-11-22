<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders;

use App\Enums\Orders\OrderCostStatusEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Mutations\FrontOffice\Orders\OrderCreateByTicketMutation;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPart;
use App\Models\Orders\OrderPayment;
use App\Models\Orders\OrderShipping;
use App\Models\Orders\OrderStatusHistory;
use App\ValueObjects\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderCreateByTicketMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_create_order_by_ticket(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $ticket = Ticket::factory()
            ->has(
                OrderCategory::factory(),
                'orderPartsRelation'
            )
            ->byTechnician()
            ->create();

        $project = $this->createProjectForMember($member);

        $categoryDefault = OrderCategory::query()->first();

        $categories = $this->createOrderCategories();

        /**@var OrderDeliveryType $deliveryType */
        $deliveryType = OrderDeliveryType::query()->first();

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
            'state_id' => $state->id,
            'country_code' => $country->country_code,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,
            'comment' => $this->faker->text,
            'parts' => [
                [
                    'id' => $categoryDefault->id,
                    'description' => $this->faker->title
                ],
                [
                    'id' => $categories[0]->id
                ],
                [
                    'id' => $categories[1]->id
                ]
            ]
        ];

        $query = new GraphQLQuery(
            OrderCreateByTicketMutation::NAME,
            [
                'ticket_id' => $ticket->id,
                'input' => $orderCreateData
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

        $orderId = $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson([
                    'data' => [
                        OrderCreateByTicketMutation::NAME => [
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
                                    'id' => (string)$categoryDefault->id,
                                    'name' => $categoryDefault->translation->title,
                                    'description' => $orderCreateData['parts'][0]['description'],
                                    'quantity' => config('orders.categories.default_quantity'),
                                    'price' => null
                                ],
                                [
                                    'id' => (string)$categories[0]->id,
                                    'name' => $categories[0]->translation->title,
                                    'description' => null,
                                    'quantity' => config('orders.categories.default_quantity'),
                                    'price' => null
                                ],
                                [
                                    'id' => (string)$categories[1]->id,
                                    'name' => $categories[1]->translation->title,
                                    'description' => null,
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
            ->json('data.' . OrderCreateByTicketMutation::NAME . '.id');

        $this->assertDatabaseHas(
            OrderStatusHistory::class,
            [
                'order_id' => $orderId,
                'status' => OrderStatusEnum::CREATED,
                'changer_id' => $member->id
            ]
        );

        $this->assertDatabaseHas(
            Order::class,
            [
                'id' => $orderId,
                'status' => OrderStatusEnum::CREATED,
                'ticket_id' => $ticket->id,
            ]
        );

        $this->assertDatabaseCount(
            OrderPart::class,
            3
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
}
