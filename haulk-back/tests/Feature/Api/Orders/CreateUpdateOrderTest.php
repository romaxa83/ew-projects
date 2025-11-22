<?php

namespace Tests\Feature\Api\Orders;

use App\Broadcasting\Events\Offers\NewOfferBroadcast;
use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\UserFactoryHelper;

class CreateUpdateOrderTest extends OrderTestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_no_required_fields_passed(): void
    {
        $this->loginAsCarrierDispatcher();

        $response = $this->postJson(route('orders.store'), $this->order_fields_create);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseMissing(
            'orders',
            [
                'user_id' => $this->authenticatedUser->id
            ]
        );
    }

    public function test_it_order_create(): void
    {
        $this->loginAsCarrierDispatcher();

        $requiredFields = $this->getRequiredFields();

        Event::fake([
            ModelChanged::class,
            NewOfferBroadcast::class
        ]);

        $response = $this
            ->postJson(
                route('orders.store'),
                $requiredFields + $this->order_fields_create
            );

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(ModelChanged::class);
        Event::assertDispatched(NewOfferBroadcast::class);

        // order created
        $this->assertDatabaseHas(
            'orders',
            Arr::except(
                $requiredFields,
                [
                    'vehicles',
                    'expenses',
                    'payment',
                    'shipper_contact',
                    'delivery_contact',
                    'pickup_contact',
                ]
            )
        );

        // get created order data
        $order = $response->json('data');

        // payment info created
        $this->assertDatabaseHas(
            'payments',
            [
                'order_id' => $order['id']
            ]
        );

        // vehicles exist
        $this->assertDatabaseHas(
            'vehicles',
            [
                'order_id' => $order['id']
            ]
        );

        // expenses exist
        $this->assertDatabaseHas(
            'expenses',
            [
                'order_id' => $order['id']
            ]
        );
    }

    public function test_it_order_updated(): void
    {
        $this->loginAsCarrierDispatcher();

        // create order
        $requiredFields = $this->getRequiredFields();

        Event::fake([
            ModelChanged::class,
            NewOfferBroadcast::class,
            UpdateOrderBroadcast::class
        ]);

        $response = $this->postJson(
            route('orders.store'),
            $requiredFields + $this->order_fields_create
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $order = $response->json('data');

        $requiredFields['vehicles'][0]['id'] = $order['vehicles'][0]['id'];
        $requiredFields['vehicles'][1]['id'] = $order['vehicles'][1]['id'];
        $requiredFields['expenses'][0]['id'] = $order['expenses'][0]['id'];
        $requiredFields['expenses'][1]['id'] = $order['expenses'][1]['id'];

        $this->postJson(
            route('orders.update-order', $order['id']),
            $requiredFields
        )
            ->assertOk();

        Event::assertDispatched(ModelChanged::class, 2);
        Event::assertDispatched(NewOfferBroadcast::class, 1);
        Event::assertDispatched(UpdateOrderBroadcast::class, 1);

        $this->assertDatabaseHas(
            'orders',
            [
                'id' => $order['id'],
                'load_id' => $this->order_fields_update['load_id'],
                'status' => $this->order_fields_update['status'],
            ]
        );

        // check vehicle updated
        $this->assertDatabaseHas(
            'vehicles',
            [
                'order_id' => $order['id'],
                'id' => $requiredFields['vehicles'][0]['id']
            ]
        );

        // check expense updated
        $this->assertDatabaseHas(
            'expenses',
            [
                'order_id' => $order['id'],
                'id' => $requiredFields['expenses'][0]['id'],
                'type_id' => $requiredFields['expenses'][0]['type_id'],
            ]
        );
    }

    public function test_it_order_delete_vehicles(): void
    {
        $this->loginAsCarrierDispatcher();

        // create order
        $requiredFields = $this->getRequiredFields();

        Event::fake([
            ModelChanged::class,
            NewOfferBroadcast::class,
            UpdateOrderBroadcast::class
        ]);

        $response = $this->postJson(
            route('orders.store'),
            $requiredFields + $this->order_fields_create
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $order = $response->json('data');

        $this->deleteJson(
            route(
                'orders.delete-vehicle',
                [
                    'order' => $order['id'],
                    'vehicle' => $order['vehicles'][0]['id']
                ]
            )
        )->assertNoContent();

        $this->deleteJson(
            route(
                'orders.delete-vehicle',
                [
                    'order' => $order['id'],
                    'vehicle' => $order['vehicles'][1]['id']
                ]
            )
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        Event::assertDispatched(ModelChanged::class, 2);
        Event::assertDispatched(NewOfferBroadcast::class, 1);
        Event::assertDispatched(UpdateOrderBroadcast::class, 1);
    }

    public function test_create_update_order_payment(): void
    {
        $this->loginAsCarrierDispatcher();

        $total = 1234;

        // test errors

        $requiredFields = $this->getRequiredFields();
        $requiredFields['payment'] = [
            'total_carrier_amount' => $total,
            'customer_payment_amount' => null,
        ];

        $this->postJson(
            route('orders.store'),
            $requiredFields
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.source.parameter', 'payment.customer_payment_amount');

        $requiredFields = $this->getRequiredFields();
        $requiredFields['payment'] = [
            'total_carrier_amount' => $total,
            'customer_payment_amount' => $total + 100,
            'customer_payment_method_id' => Payment::METHOD_USHIP,
            'customer_payment_location' => Order::LOCATION_PICKUP,
        ];

        $this->postJson(
            route('orders.store'),
            $requiredFields
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.source.parameter', 'payment.broker_fee_amount');

        // test successful save

        $requiredFields = $this->getRequiredFields();

        $response = $this->postJson(
            route('orders.store'),
            $requiredFields
        )
            ->assertCreated();

        $requiredFields['payment'] = [
            'total_carrier_amount' => $total,
            'broker_payment_amount' => $total,
            'broker_payment_method_id' => Payment::METHOD_USHIP,
            'broker_payment_days' => 5,
            'broker_payment_begins' => Order::LOCATION_PICKUP,
        ];

        $this->postJson(
            route(
                'orders.update-order',
                $response->json('data.id')
            ),
            $requiredFields
        )
            ->assertOk()
            ->assertJsonPath('data.payment.customer_payment_amount', null)
            ->assertJsonPath('data.payment.customer_payment_method_id', null)
            ->assertJsonPath('data.payment.customer_payment_location', null);
    }

    public function test_it_order_create_with_tags(): void
    {
        $this->loginAsCarrierDispatcher();

        $requiredFields = $this->getRequiredFields();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tags = [
            'tags' => [$tag1->id, $tag2->id],
        ];

        Event::fake([
            ModelChanged::class,
            NewOfferBroadcast::class
        ]);

        $response = $this
            ->postJson(
                route('orders.store'),
                $requiredFields + $this->order_fields_create + $tags
            );

        $response->assertStatus(Response::HTTP_CREATED);

        // get created order data
        $order = $response->json('data');

        //tags exists
        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag1->id,
                'taggable_id' => $order['id'],
                'taggable_type' => Order::class,
            ]
        );

        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag2->id,
                'taggable_id' => $order['id'],
                'taggable_type' => Order::class,
            ]
        );
    }

    public function test_it_order_create_with_tags_validation(): void
    {
        $this->loginAsCarrierDispatcher();

        $requiredFields = $this->getRequiredFields();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $tags = [
            'tags' => [$tag1->id, $tag2->id, $tag3->id],
        ];

        Event::fake([
            ModelChanged::class,
            NewOfferBroadcast::class
        ]);

        $this
            ->postJson(
                route('orders.store'),
                $requiredFields + $this->order_fields_create + $tags
            )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'tags'],
                            'title' => 'The Tags may not have more than 2 items.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                    ],
                ]
            );

        $tags = [
            'tags' => [111],
        ];
        $this
            ->postJson(
                route('orders.store'),
                $requiredFields + $this->order_fields_create + $tags
            )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'tags.0'],
                            'title' => 'The selected Tag is invalid.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ],
                    ],
                ]
            );

    }

    public function test_it_order_updated_with_tags(): void
    {
        $this->loginAsCarrierDispatcher();

        // create order
        $requiredFields = $this->getRequiredFields();

        Event::fake([
            ModelChanged::class,
            NewOfferBroadcast::class,
            UpdateOrderBroadcast::class
        ]);

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tags = [
            'tags' => [$tag1->id, $tag2->id],
        ];

        $response = $this->postJson(
            route('orders.store'),
            $requiredFields + $this->order_fields_create + $tags
        );

        $response->assertStatus(Response::HTTP_CREATED);

        $order = $response->json('data');

        $tag3 = Tag::factory()->create();
        $tags = [
            'tags' => [$tag2->id, $tag3->id],
        ];

        $res = $this->postJson(
            route('orders.update-order', $order['id']),
            $requiredFields + $this->order_fields_update + $tags
        )
            ->assertOk();

        // Order has new tags
        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag2->id,
                'taggable_id' => $order['id'],
                'taggable_type' => Order::class,
            ]
        );

        $this->assertDatabaseHas(
            'taggables',
            [
                'tag_id' => $tag3->id,
                'taggable_id' => $order['id'],
                'taggable_type' => Order::class,
            ]
        );

        $this->assertDatabaseMissing(
            'taggables',
            [
                'tag_id' => $tag1->id,
                'taggable_id' => $order['id'],
                'taggable_type' => Order::class,
            ]
        );
    }

    public function test_it_order_create_with_cash_and_credit_card_payment_methods(): void
    {
        $this->loginAsCarrierDispatcher();

        $requiredFields = $this->getRequiredFields();
        $requiredFields['payment'] = [
            'total_carrier_amount' => 500,
            'customer_payment_amount' => 500,
            'customer_payment_method_id' => Payment::METHOD_CASH,
            'broker_payment_amount' => 500,
            'broker_payment_days' => 2,
            'broker_payment_begins' => Order::LOCATION_PICKUP,
            'customer_payment_location' => Order::LOCATION_PICKUP,
            'broker_payment_method_id' => Payment::METHOD_CASH,
            'broker_fee_amount' => 500,
            'broker_fee_days' => 2,
            'broker_fee_method_id' => Payment::METHOD_CASH,
            'broker_fee_begins' => Order::LOCATION_PICKUP,
        ];

        $response = $this
            ->postJson(
                route('orders.store'),
                $requiredFields + $this->order_fields_create
            )->assertCreated();

        // get created order data
        $order = $response->json('data');

        // payment info created
        $this->assertDatabaseHas(
            'payments',
            [
                'order_id' => $order['id'],
                'customer_payment_method_id' => Payment::METHOD_CASH,
                'broker_payment_method_id' => Payment::METHOD_CASH,
                'broker_fee_method_id' => Payment::METHOD_CASH,
            ]
        );

        $requiredFields['payment'] = [
            'total_carrier_amount' => 500,
            'customer_payment_amount' => 500,
            'customer_payment_method_id' => Payment::METHOD_CREDIT_CARD,
            'broker_payment_amount' => 500,
            'broker_payment_days' => 2,
            'broker_payment_begins' => Order::LOCATION_PICKUP,
            'customer_payment_location' => Order::LOCATION_PICKUP,
            'broker_payment_method_id' => Payment::METHOD_CREDIT_CARD,
            'broker_fee_amount' => 500,
            'broker_fee_days' => 2,
            'broker_fee_method_id' => Payment::METHOD_CREDIT_CARD,
            'broker_fee_begins' => Order::LOCATION_PICKUP,
        ];

        $response = $this
            ->postJson(
                route('orders.store'),
                $requiredFields + $this->order_fields_create
            )->assertCreated();

        // get created order data
        $order = $response->json('data');

        // payment info created
        $this->assertDatabaseHas(
            'payments',
            [
                'order_id' => $order['id'],
                'customer_payment_method_id' => Payment::METHOD_CREDIT_CARD,
                'broker_payment_method_id' => Payment::METHOD_CREDIT_CARD,
                'broker_fee_method_id' => Payment::METHOD_CREDIT_CARD,
            ]
        );
    }

    public function test_it_order_create_with_owner_driver(): void
    {
        $this->loginAsCarrierDispatcher();

        $requiredFields = $this->getRequiredFields();

        Event::fake([
            ModelChanged::class,
        ]);

        $driver = $this->driverOwnerFactory();
        $dispatcher = $this->dispatcherFactory();
        $additionalFields = [
            'driver_id' => $driver->id,
            'dispatcher_id' => $dispatcher->id,
        ];

        $response = $this
            ->postJson(
                route('orders.store'),
                $requiredFields + $this->order_fields_create + $additionalFields
            );

        $response->assertStatus(Response::HTTP_CREATED);

        Event::assertDispatched(ModelChanged::class);

        // order created
        $this->assertDatabaseHas(
            'orders',
            Arr::except(
                $requiredFields,
                [
                    'vehicles',
                    'expenses',
                    'payment',
                    'shipper_contact',
                    'delivery_contact',
                    'pickup_contact',
                ]
            )
        );
    }
}
