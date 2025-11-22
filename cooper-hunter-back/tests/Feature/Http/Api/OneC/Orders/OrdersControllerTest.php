<?php

namespace Tests\Feature\Http\Api\OneC\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;

class OrdersControllerTest extends TestCase
{
    use DatabaseTransactions;
    use OrderCreateTrait;
    use WithFaker;

    public function test_list(): void
    {
        $this->loginAsModerator();

        $this->createAllStatusesOrder();

        $this->getJson(route('1c.orders.index'))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(
                [
                    'data' => [$this->getJsonStructure()]
                ],
            );
    }

    protected function getJsonStructure(): array
    {
        return [
            'id',
            'arrived_from',
            'status',
            'serial_number',
            'model',
            'first_name',
            'last_name',
            'phone',
            'comment',
            'parts' => [
                [
                    'order_category_id',
                    'name',
                    'quantity',
                    'price',
                    'description',
                ]
            ],
            'shipping' => [
                'order_id',
                'first_name',
                'last_name',
                'phone',
                'address_first_line',
                'address_second_line',
                'city',
                'country' => [
                    'id',
                    'slug',
                    'active',
                    'default',
                    'country_code',
                    'translations' => [
                        [
                            'id',
                            'name',
                            'language',
                        ]
                    ],
                ],
                'state' => [
                    'id',
                    'short_name',
                    'status',
                    'hvac_license',
                    'epa_license',
                    'translations' => [
                        [
                            'id',
                            'name',
                            'language',
                        ]
                    ],
                ],
                'zip',
                'trk_number' => [
                    'number',
                    'tracking_url',
                ],
                'delivery_type' => [
                    'id',
                    'translations' => [
                        [
                            'id',
                            'title',
                            'description',
                        ]
                    ],
                ],
            ],
            'payment' => [
                'cost_status',
                'order_id',
                'order_price',
                'order_price_with_discount',
                'shipping_cost',
                'tax',
                'discount',
                'paid_at',
            ],
        ];
    }

    public function test_get_orders_with_status_created(): void
    {
        $this->loginAsModerator();

        $this->createAllStatusesOrder();

        $this->getJson(
            route(
                '1c.orders.index',
                [
                    'status' => OrderStatusEnum::CREATED
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(
                [
                    'data' => [$this->getJsonStructure()]
                ],
            );
    }

    public function test_update(): void
    {
        $this->loginAsModerator();

        /**@var OrderDeliveryType $deliveryType */
        $deliveryType = OrderDeliveryType::query()->first();

        $category = OrderCategory::factory()->create();

        $order = $this->createCreatedOrder();

        $state = State::first();
        $country = Country::first();

        $orderUpdateData = [
            'order' => $order->id,
            'status' => OrderStatusEnum::PENDING_PAID,
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
                    'order_category_guid' => $category->guid,
                    'price' => 100.00,
                    'description' => null,
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

        $this->putJson(route('1c.orders.update', $orderUpdateData))
            ->assertOk()
            ->assertJsonStructure([
                'data' => $this->getJsonStructure()
            ]);
    }
}
