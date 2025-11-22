<?php

namespace Tests\Feature\Mutations\BackOffice\Orders;

use App\GraphQL\Mutations\BackOffice\Orders\OrderShippingUpdateMutation;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Permissions\Orders\OrderUpdatePermission;
use App\ValueObjects\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderShippingUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function test_update_shipping_data(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createCreatedOrder();

        /**@var OrderDeliveryType $deliveryType */
        $deliveryType = OrderDeliveryType::query()
            ->first();

        $state = State::first();
        $country = Country::first();

        $shippingUpdate = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'address_first_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,

        ];

        $query = new GraphQLQuery(
            OrderShippingUpdateMutation::NAME,
            [
                'id' => $order->id,
                'shipping' => $shippingUpdate
            ],
            [
                'id',
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
                        'translation' => [
                            'id',
                        ],
                    ],
                    'trk_number' => [
                        'number',
                    ]
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderShippingUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'shipping' => [
                                'first_name' => $shippingUpdate['first_name'],
                                'last_name' => $shippingUpdate['last_name'],
                                'phone' => new Phone($shippingUpdate['phone']),
                                'address_first_line' => $shippingUpdate['address_first_line'],
                                'address_second_line' => null,
                                'city' => $shippingUpdate['city'],
                                'country' => [
                                    'id' => $country->id
                                ],
                                'state' => [
                                    'id' => $state->id
                                ],
                                'zip' => $shippingUpdate['zip'],
                                'deliveryType' => [
                                    'translation' => [
                                        'id' => (string)$deliveryType->translation->id,
                                    ]
                                ],
                                'trk_number' => null
                            ]
                        ]
                    ]
                ]
            );
    }

    public function test_update_shipping_data_on_shipped_order(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createShippedOrder();

        /**@var OrderDeliveryType $deliveryType */
        $deliveryType = OrderDeliveryType::query()
            ->first();

        $state = State::first();
        $country = Country::first();

        $shippingUpdate = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'address_first_line' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'zip' => (string)$this->faker->randomNumber(5),
            'delivery_type' => $deliveryType->id,
            'trk_number' => (string)$this->faker->numberBetween(10000, 100000000),
        ];

        $query = new GraphQLQuery(
            OrderShippingUpdateMutation::NAME,
            [
                'id' => $order->id,
                'shipping' => $shippingUpdate
            ],
            [
                'id',
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
                        'translation' => [
                            'id',
                        ],
                    ],
                    'trk_number' => [
                        'number',
                    ]
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderShippingUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'shipping' => [
                                'first_name' => $shippingUpdate['first_name'],
                                'last_name' => $shippingUpdate['last_name'],
                                'phone' => new Phone($shippingUpdate['phone']),
                                'address_first_line' => $shippingUpdate['address_first_line'],
                                'address_second_line' => null,
                                'city' => $shippingUpdate['city'],
                                'country' => [
                                    'id' => $country->id
                                ],
                                'state' => [
                                    'id' => $state->id
                                ],
                                'zip' => $shippingUpdate['zip'],
                                'deliveryType' => [
                                    'translation' => [
                                        'id' => (string)$deliveryType->translation->id,
                                    ]
                                ],
                                'trk_number' => [
                                    'number' => $shippingUpdate['trk_number']
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }
}
