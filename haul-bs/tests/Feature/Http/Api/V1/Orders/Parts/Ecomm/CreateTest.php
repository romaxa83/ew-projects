<?php

namespace Tests\Feature\Http\Api\V1\Orders\Parts\Ecomm;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Enums\Orders\Parts\ShippingMethod;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Models\Customers\Customer;
use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Order;
use App\Services\Payments\PaypalService;
use App\Services\Payments\StripeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected InventoryBuilder $inventoryBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);

        parent::setUp();

        $customer = $this->customerBuilder->create();
        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $this->data = [
            'customer_id' => $customer->id,
            'client' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'jhon@doe.com',
            ],
            'items' => [
                [
                    'inventory_id' => $inventory->id,
                    'quantity' => 5,
                ]
            ],
            'delivery_type' => DeliveryType::Delivery(),
            'delivery_address' => [
                'first_name' => 'first',
                'last_name' => 'last',
                'address' => 'd address',
                'company' => 'd company',
                'city' => 'd city',
                'state' => 'd state',
                'zip' => '44444',
                'phone' => '75665654544',
            ],
            'billing_address' => [
                'first_name' => 'firster',
                'last_name' => 'laster',
                'address' => 'b address',
                'company' => 'b company',
                'city' => 'b city',
                'state' => 'b state',
                'zip' => '44444',
                'phone' => '95665654544',
            ],
            'payment_method' => PaymentMethod::PayPal(),
            'with_tax_exemption' => true
        ];
    }

    private function mockingPaymentPaypalService(): void
    {
        $mock = $this->createMock(PaypalService::class);
        $mock->expects($this->once())
            ->method('createPaymentOrder')
            ->will($this->returnValue('http://localhost'));
        $this->app->instance(PaypalService::class, $mock);
    }

    private function mockingPaymentStripeService(): void
    {
        $mock = $this->createMock(StripeService::class);
        $mock->expects($this->once())
            ->method('getCheckoutUrl')
            ->will($this->returnValue('http://localhost'));
        $this->app->instance(StripeService::class, $mock);
    }

    /** @test */
    public function success_create_paypal()
    {
        $this->mockingPaymentPaypalService();

        $data = $this->data;

        $id = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'order_number',
                    'status_changed_at',
                ]
            ])
            ->assertJson([
                'link' => 'http://localhost',
                'order' => [
                    'customer' => [
                        'id' => $data['customer_id'],
                    ],
                    'sales_manager' => null,
                    'status' => OrderStatus::New->value,
                    'paid_at' => null,
                    'delivery_address' => [
                        'first_name' => $data['delivery_address']['first_name'],
                        'last_name' => $data['delivery_address']['last_name'],
                        'company' => $data['delivery_address']['company'],
                        'address' => $data['delivery_address']['address'],
                        'city' => $data['delivery_address']['city'],
                        'state' => $data['delivery_address']['state'],
                        'zip' => $data['delivery_address']['zip'],
                        'phone' => $data['delivery_address']['phone'],
                    ],
                    'billing_address' => [
                        'first_name' => $data['billing_address']['first_name'],
                        'last_name' => $data['billing_address']['last_name'],
                        'company' => $data['billing_address']['company'],
                        'address' => $data['billing_address']['address'],
                        'city' => $data['billing_address']['city'],
                        'state' => $data['billing_address']['state'],
                        'zip' => $data['billing_address']['zip'],
                        'phone' => $data['billing_address']['phone'],
                    ],
                    'source' => OrderSource::Haulk_Depot(),
                    'is_refunded' => false,
                    'is_draft' => false,
                    'delivery_type' => $data['delivery_type'],
                    'items' => [
                        [
                            'quantity' => $data['items'][0]['quantity'],
                            'inventory' => [
                                'id' => $data['items'][0]['inventory_id'],
                            ]
                        ]
                    ],
                    'shipping_methods' => [
                        [
                            'name' => ShippingMethod::UPS_Standard(),
                            'cost' => 0,
                            'terms' => null,
                        ]
                    ],
                    'payment' => [
                        'method' => $data['payment_method'],
                        'terms' => null,
                        'with_tax_exemption' => $data['with_tax_exemption'],
                    ],
                    'ecommerce_client' => [
                        'first_name' => $data['client']['first_name'],
                        'last_name' => $data['client']['last_name'],
                        'email' => $data['client']['email'],
                    ]
                ]
            ])
            ->assertJsonCount(1,'order.items')
            ->assertJsonCount(1,'order.shipping_methods')
            ->assertJsonCount(0,'order.deliveries')
            ->json('order.id')
        ;

        $model = Order::find($id);

        $this->assertNull($model->delivered_at);
        $this->assertNull($model->past_due_at);

        $this->assertNotNull($model->total_amount);
        $this->assertNotNull($model->paid_amount);
        $this->assertNotNull($model->debt_amount);

        $this->assertEquals($model->ecommerce_client_email, $data['client']['email']);
        $this->assertEquals(
            $model->ecommerce_client_name,
            $data['client']['first_name'] .' '. $data['client']['last_name']
        );
    }
    public function success_create_stripe(): void
    {
        $this->mockingPaymentStripeService();

        $data = $this->data;
        $data['payment_method'] = PaymentMethod::Card();

        $id = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'order_number',
                    'status_changed_at',
                ]
            ])
            ->assertJson([
                'link' => 'http://localhost',
                'order' => [
                    'customer' => [
                        'id' => $data['customer_id'],
                    ],
                    'sales_manager' => null,
                    'status' => OrderStatus::New->value,
                    'paid_at' => null,
                    'delivery_address' => [
                        'first_name' => $data['delivery_address']['first_name'],
                        'last_name' => $data['delivery_address']['last_name'],
                        'company' => $data['delivery_address']['company'],
                        'address' => $data['delivery_address']['address'],
                        'city' => $data['delivery_address']['city'],
                        'state' => $data['delivery_address']['state'],
                        'zip' => $data['delivery_address']['zip'],
                        'phone' => $data['delivery_address']['phone'],
                    ],
                    'billing_address' => [
                        'first_name' => $data['billing_address']['first_name'],
                        'last_name' => $data['billing_address']['last_name'],
                        'company' => $data['billing_address']['company'],
                        'address' => $data['billing_address']['address'],
                        'city' => $data['billing_address']['city'],
                        'state' => $data['billing_address']['state'],
                        'zip' => $data['billing_address']['zip'],
                        'phone' => $data['billing_address']['phone'],
                    ],
                    'source' => OrderSource::Haulk_Depot(),
                    'is_refunded' => false,
                    'is_draft' => false,
                    'delivery_type' => $data['delivery_type'],
                    'items' => [
                        [
                            'quantity' => $data['items'][0]['quantity'],
                            'inventory' => [
                                'id' => $data['items'][0]['inventory_id'],
                            ]
                        ]
                    ],
                    'shipping_methods' => [
                        [
                            'name' => ShippingMethod::UPS_Standard(),
                            'cost' => 0,
                            'terms' => null,
                        ]
                    ],
                    'payment' => [
                        'method' => $data['payment_method'],
                        'terms' => null,
                        'with_tax_exemption' => $data['with_tax_exemption'],
                    ],
                    'ecommerce_client' => [
                        'first_name' => $data['client']['first_name'],
                        'last_name' => $data['client']['last_name'],
                        'email' => $data['client']['email'],
                    ]
                ]
            ])
            ->assertJsonCount(1,'order.items')
            ->assertJsonCount(1,'order.shipping_methods')
            ->assertJsonCount(0,'order.deliveries')
            ->json('order.id')
        ;

        $model = Order::find($id);

        $this->assertNull($model->delivered_at);
        $this->assertNull($model->past_due_at);

        $this->assertNotNull($model->total_amount);
        $this->assertNotNull($model->paid_amount);
        $this->assertNotNull($model->debt_amount);

        $this->assertEquals($model->ecommerce_client_email, $data['client']['email']);
        $this->assertEquals(
            $model->ecommerce_client_name,
            $data['client']['first_name'] .' '. $data['client']['last_name']
        );
    }

    /** @test */
    public function success_create_check_history()
    {
        $this->mockingPaymentPaypalService();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $inventory = $this->inventoryBuilder->quantity(10)->create();

        $data = $this->data;
        $data['customer_id'] = $customer->id;
        $data['items'][0] = [
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ];

        $id = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)

            ->assertJson([
                'order' => [
                    'customer' => [
                        'id' => $data['customer_id'],
                    ],
                ],
            ])
            ->json('order.id')
        ;

        /** @var $model Order */
        $model = Order::find($id);

        $this->assertCount(1, $model->histories);
        $history = $model->histories[0];
        $item = $model->items[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertNull($history->user_id);
        $this->assertEquals($history->msg, 'history.order.common.created');
        $this->assertEquals($history->msg_attr, [
            'role' => null,
            'email' => null,
            'full_name' => null,
            'user_id' => null,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details['customer_id'], [
            'old' => null,
            'new' => $customer->full_name,
            'type' => 'added',
        ]);

        $this->assertEquals($history->details['order_number'], [
            'old' => null,
            'new' => $model->order_number,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['status'], [
            'old' => null,
            'new' => $model->status->value,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['source'], [
            'old' => null,
            'new' => $model->source->value,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_type'], [
            'old' => null,
            'new' => $model->delivery_type->value,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['payment_method'], [
            'old' => null,
            'new' => $model->payment_method->value,
            'type' => 'added',
        ]);
        // delivery address
        $this->assertEquals($history->details['delivery_address.first_name'], [
            'old' => null,
            'new' => $model->delivery_address->first_name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.last_name'], [
            'old' => null,
            'new' => $model->delivery_address->last_name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.company'], [
            'old' => null,
            'new' => $model->delivery_address->company,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.address'], [
            'old' => null,
            'new' => $model->delivery_address->address,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.city'], [
            'old' => null,
            'new' => $model->delivery_address->city,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.state'], [
            'old' => null,
            'new' => $model->delivery_address->state,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.zip'], [
            'old' => null,
            'new' => $model->delivery_address->zip,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.phone'], [
            'old' => null,
            'new' => $model->delivery_address->phone->getValue(),
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['delivery_address.save'], [
            'old' => null,
            'new' => false,
            'type' => 'added',
        ]);
        // billing address
        $this->assertEquals($history->details['billing_address.first_name'], [
            'old' => null,
            'new' => $model->billing_address->first_name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.last_name'], [
            'old' => null,
            'new' => $model->billing_address->last_name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.company'], [
            'old' => null,
            'new' => $model->billing_address->company,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.address'], [
            'old' => null,
            'new' => $model->billing_address->address,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.city'], [
            'old' => null,
            'new' => $model->billing_address->city,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.state'], [
            'old' => null,
            'new' => $model->billing_address->state,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.zip'], [
            'old' => null,
            'new' => $model->billing_address->zip,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.phone'], [
            'old' => null,
            'new' => $model->billing_address->phone->getValue(),
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['billing_address.save'], [
            'old' => null,
            'new' => false,
            'type' => 'added',
        ]);

        // items
        $this->assertEquals($history->details['items.'.$item->id.'.inventories.'.$inventory->id.'.name'], [
            'old' => null,
            'new' => $inventory->name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['items.'.$item->id.'.inventories.'.$inventory->id.'.quantity'], [
            'old' => null,
            'new' => $data['items'][0]['quantity'],
            'type' => 'added',
        ]);
    }

    /** @test */
    public function success_create_check_reserveForOrder()
    {
        $this->mockingPaymentPaypalService();

        $wasQty = 10;
        $reserveQty = 4;

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity($wasQty)->create();

        $data = $this->data;
        $data['items'][0] = [
            'inventory_id' => $inventory->id,
            'quantity' => $reserveQty,
        ];
        unset($data['customer_id']);

        $this->assertEmpty($inventory->transactions);
        $this->assertEmpty($inventory->histories);

        $id = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
            ->assertJson([
                'order' => [
                    'customer' => null,
                ],
            ])
            ->json('order.id')
        ;

        /** @var $model Order */
        $model = Order::find($id);

        $inventory->refresh();

        $this->assertCount(1, $inventory->transactions);

        $this->assertNull($inventory->transactions[0]->order_id);
        $this->assertEquals($inventory->transactions[0]->inventory_id, $inventory->id);
        $this->assertEquals($inventory->transactions[0]->operation_type->value, OperationType::SOLD->value);
        $this->assertEquals($inventory->transactions[0]->invoice_number, $model->order_number);
        $this->assertEquals($inventory->transactions[0]->price, $inventory->price_retail);
        $this->assertEquals($inventory->transactions[0]->quantity, $reserveQty);
        $this->assertEquals($inventory->transactions[0]->order_parts_id, $model->id);
        $this->assertTrue($inventory->transactions[0]->is_reserve);

        $this->assertEquals($inventory->quantity, $wasQty - $reserveQty);

        $this->assertCount(1, $inventory->histories);

        $this->assertEquals($inventory->transactions[0]->order_parts_id, $model->id);

        /** @var $history History */
        $history = $inventory->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertNull($history->user_id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reserved_for_order');

        $this->assertEquals($history->msg_attr, [
            'role' => null,
            'full_name' => null,
            'email' => null,
            'stock_number' => $inventory->stock_number,
            'inventory_name' => $inventory->name,
            'user_id' => null,
            'price' => '$' . number_format($inventory->price_retail, 2),
            'order_link' => str_replace('{id}', $model->id, config('routes.front.parts_order_show_url')),
            'order_number' => $model->order_number,
        ]);

        $this->assertEquals($history->details['quantity'], [
            'old' => $wasQty,
            'new' => $wasQty - $reserveQty,
            'type' => 'updated',
        ]);
    }

    /** @test */
    public function success_create_without_delivery_address_as_pickup()
    {
        $this->mockingPaymentPaypalService();

        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Pickup();
        unset($data['delivery_address']);

        $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
            ->assertJson([
                'order' => [
                    'customer' => [
                        'id' => $data['customer_id'],
                    ],
                    'delivery_address' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_create_without_delivery_address_as_delivery()
    {
        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Delivery();
        unset($data['delivery_address']);

        $res = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
        ;

        $this->assertValidationMsg(
            $res,
            __('validation.required', ['attribute' => 'Delivery Address']),
            'delivery_address'
        );
    }

    /** @test */
    public function success_create_without_billing_address_as_delivery()
    {
        $this->mockingPaymentPaypalService();

        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Delivery();
        unset($data['billing_address']);

        $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
            ->assertJson([
                'order' => [
                    'customer' => [
                        'id' => $data['customer_id'],
                    ],
                    'billing_address' => [
                        'first_name' => $data['delivery_address']['first_name'],
                        'last_name' => $data['delivery_address']['last_name'],
                        'company' => $data['delivery_address']['company'],
                        'address' => $data['delivery_address']['address'],
                        'city' => $data['delivery_address']['city'],
                        'state' => $data['delivery_address']['state'],
                        'zip' => $data['delivery_address']['zip'],
                        'phone' => $data['delivery_address']['phone'],
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function fail_create_without_billing_address_as_pickup()
    {
        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Pickup();
        unset($data['billing_address']);

        $res = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
        ;

        $this->assertValidationMsg(
            $res,
            __('validation.required', ['attribute' => 'Billing Address']),
            'billing_address'
        );
    }

    /** @test */
    public function fail_create_enough_inventory_qty()
    {
        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity(5)->create();

        $data = $this->data;
        $data['items'][0] = [
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ];

        $res = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data)
        ;

        $this->assertValidationMsg(
            $res,
            __("validation.custom.order.parts.few_quantities"),
            'items.0.quantity'
        );
    }

    /** @test */
    public function wrong_token()
    {
        $data = $this->data;

        $res = $this->postJsonEComm(route('api.v1.e_comm.orders.store'), $data, [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
