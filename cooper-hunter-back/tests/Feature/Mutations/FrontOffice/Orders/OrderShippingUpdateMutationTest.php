<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders;

use App\GraphQL\Mutations\FrontOffice\Orders\OrderShippingUpdateMutation;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\ValueObjects\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderShippingUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_update_shipping_data(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createCreatedOrder();

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
                    'country' => [
                        'id'
                    ],
                    'state' => [
                        'id'
                    ],
                    'city',
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
                ]
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderShippingUpdateMutation::NAME => [
                            'id' => $order->id,
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
                            ]
                        ]
                    ]
                ]
            );
    }
}
