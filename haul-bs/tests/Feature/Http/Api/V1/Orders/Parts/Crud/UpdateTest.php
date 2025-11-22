<?php

namespace Feature\Http\Api\V1\Orders\Parts\Crud;

use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Enums\Orders\Parts\PaymentTerms;
use App\Events\Events\Orders\Parts\RequestToEcom;
use App\Events\Listeners\Orders\Parts\RequestToEcomListener;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Orders\Parts\ShippingBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected ShippingBuilder $shippingBuilder;
    protected UserBuilder $userBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected CustomerBuilder $customerBuilder;
    protected AddressBuilder $addressBuilder;

    protected $data = [];
    protected $fullData = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->shippingBuilder = resolve(ShippingBuilder::class);
        $this->addressBuilder = resolve(AddressBuilder::class);

        $customer = $this->customerBuilder->create();

        $item = $this->itemBuilder->create();

        $this->data = [
            'source' => OrderSource::Amazon(),
            'customer_id' => $customer->id
        ];
        $this->fullData = [
            'source' => OrderSource::Amazon(),
            'customer_id' => $customer->id,
            'delivery_type' => DeliveryType::Pickup(),
            'delivery_address' => [
                'first_name' => 'first',
                'last_name' => 'last',
                'address' => 'some address',
                'company' => 'company',
                'city' => 'Sacramento',
                'state' => 'CA',
                'zip' => '99808',
                'phone' => '9811111111',
            ],
            'billing_address' => [
                'first_name' => 'bfirst',
                'last_name' => 'blast',
                'address' => 'bsome address',
                'company' => 'bcompany',
                'city' => 'bSacramento',
                'state' => 'bCA',
                'zip' => '99808',
                'phone' => '9816111111',
            ],
            'payment' => [
                'method' => PaymentMethod::Cash(),
                'terms' => PaymentTerms::Immediately(),
                'with_tax_exemption' => true,
            ],
            'delivery_cost' => 10,
//            'shipping_methods' => [
//                [
//                    'name' => ShippingMethod::Pickup(),
//                    'cost' => 0,
//                    'items_ids' => [
//                        $item->id
//                    ]
//                ]
//            ]
        ];
    }

    /** @test */
    public function success_update_required_field_as_draft()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $customer = $this->customerBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->delivery_address([])
            ->billing_address([])
            ->create();

        $data = $this->data;
        $data['customer_id'] = $customer->id;

        $this->assertNotEquals($model->customer_id, data_get($data, 'customer_id'));
        $this->assertNotEquals($model->source, data_get($data, 'source'));

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'customer' => [
                        'id' => $data['customer_id'],
                    ],
                    'source' => $data['source'],
                    'delivery_address' => null,
                    'billing_address' => null,
                    'delivery_type' => null,
                    'items' => [],
                    'shipping_methods' => [],
                    'payment' => null,
                ],
            ])
            ->assertJsonCount(0, 'data.shipping_methods')
            ->assertJsonCount(0, 'data.items')
        ;

        $model->refresh();
        $this->assertEmpty($model->histories);

        Event::assertNotDispatched(RequestToEcom::class);
    }

    /** @test */
    public function success_update_required_field_as_not_draft()
    {
        Event::fake([RequestToEcom::class]);

        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $customer = $this->customerBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->source(OrderSource::Haulk_Depot)
            ->delivery_type(DeliveryType::Pickup)
            ->create();

        $item = $this->itemBuilder->order($model)->inventory($inventory)->create();

        $data = $this->data;
        $data['source'] = $model->source->value;
        $data['customer_id'] = $customer->id;
        $data['delivery_type'] = DeliveryType::Delivery();
        $data['delivery_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
        ];
        $data['payment'] = [
            'method' => PaymentMethod::Wire(),
            'terms' => PaymentTerms::Day_30(),
            'with_tax_exemption' => true,
        ];
        $data['delivery_cost'] = 10;

        $this->assertNotEquals($model->customer_id, $data['customer_id']);

        $this->assertNotEquals($model->delivery_type, $data['delivery_type']);

        $this->assertNotEquals($model->delivery_address->first_name, $data['delivery_address']['first_name']);
        $this->assertNotEquals($model->delivery_address->last_name, $data['delivery_address']['last_name']);
        $this->assertNotEquals($model->delivery_address->company, $data['delivery_address']['company']);
        $this->assertNotEquals($model->delivery_address->state, $data['delivery_address']['state']);
        $this->assertNotEquals($model->delivery_address->city, $data['delivery_address']['city']);
        $this->assertNotEquals($model->delivery_address->address, $data['delivery_address']['address']);
        $this->assertNotEquals($model->delivery_address->zip, $data['delivery_address']['zip']);
        $this->assertNotEquals($model->delivery_address->phone, $data['delivery_address']['phone']);

        $this->assertNotEquals($model->payment_method, $data['payment']['method']);
        $this->assertNotEquals($model->payment_terms, $data['payment']['terms']);
        $this->assertNotEquals($model->with_tax_exemption, $data['payment']['with_tax_exemption']);

        $this->assertNotEquals($model->delivery_cost, $data['delivery_cost']);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'customer' => [
                        'id' => $data['customer_id'],
                    ],
                    'source' => $data['source'],
                    'delivery_type' => $data['delivery_type'],
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
                    'billing_address' => null,
                    'payment' => [
                        'method' => $data['payment']['method'],
                        'terms' => $data['payment']['terms'],
                        'with_tax_exemption' => $data['payment']['with_tax_exemption'],
                    ],
                    'delivery_cost' => $data['delivery_cost'],
                ],
            ])
        ;

        Event::assertDispatched(fn (RequestToEcom $event) =>
            $event->getModel()->id === $model->id
            && $event->getAction() == OrderPartsHistoryService::ACTION_UPDATE
        );
        Event::assertListening(
            RequestToEcom::class,
            RequestToEcomListener::class
        );
    }

    /** @test */
    public function success_update_as_not_draft_without_customer()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->customer(null)
            ->source(OrderSource::Haulk_Depot)
            ->delivery_type(DeliveryType::Pickup)
            ->create();

        $this->itemBuilder->order($model)->inventory($inventory)->create();

        $data = $this->data;
        $data['source'] = $model->source->value;
        $data['delivery_type'] = DeliveryType::Delivery();
        $data['delivery_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
        ];
        $data['payment'] = [
            'method' => PaymentMethod::Wire(),
            'terms' => PaymentTerms::Day_30(),
            'with_tax_exemption' => true,
        ];
        unset($data['customer_id']);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'customer' => null,
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_required_field_as_not_draft_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->create();
        $customer = $this->customerBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();
        $oldCustomerName = $model->customer->full_name;
        $clone = clone $model;

        $item = $this->itemBuilder->order($model)->inventory($inventory)->create();

        $data = $this->data;
        $data['customer_id'] = $customer->id;
        $data['delivery_type'] = DeliveryType::Delivery();
        $data['delivery_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => $model->delivery_address->company,
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
        ];
        $data['payment'] = [
            'method' => PaymentMethod::Wire(),
            'terms' => PaymentTerms::Day_30(),
            'with_tax_exemption' => true,
        ];

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model = $model->refresh();

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.updated');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

//        dd($history->details);

        $this->assertEquals($history->details['customer_id'], [
            'old' => $oldCustomerName,
            'new' => $customer->full_name,
            'type' => 'updated',
        ]);

        $this->assertEquals($history->details['source'], [
            'old' => $clone->source->value,
            'new' => $data['source'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery_type'], [
            'old' => $clone->delivery_type,
            'new' => $data['delivery_type'],
            'type' => 'updated',
        ]);

        // payment
        $this->assertEquals($history->details['payment_method'], [
            'old' => $clone->payment_method->value,
            'new' => $data['payment']['method'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['payment_terms'], [
            'old' => $clone->payment_terms->value,
            'new' => $data['payment']['terms'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['with_tax_exemption'], [
            'old' => $clone->with_tax_exemption,
            'new' => $data['payment']['with_tax_exemption'],
            'type' => 'updated',
        ]);

        // delivery address
        $this->assertEquals($history->details['delivery_address.first_name'], [
            'old' => $clone->delivery_address->first_name,
            'new' => $data['delivery_address']['first_name'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery_address.last_name'], [
            'old' => $clone->delivery_address->last_name,
            'new' => $data['delivery_address']['last_name'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery_address.address'], [
            'old' => $clone->delivery_address->address,
            'new' => $data['delivery_address']['address'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery_address.city'], [
            'old' => $clone->delivery_address->city,
            'new' => $data['delivery_address']['city'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery_address.state'], [
            'old' => $clone->delivery_address->state,
            'new' => $data['delivery_address']['state'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery_address.zip'], [
            'old' => $clone->delivery_address->zip,
            'new' => $data['delivery_address']['zip'],
            'type' => 'updated',
        ]);
        $this->assertEquals($history->details['delivery_address.phone'], [
            'old' => $clone->delivery_address->phone,
            'new' => $data['delivery_address']['phone'],
            'type' => 'updated',
        ]);

        $this->assertEquals($history->details['total_amount'], [
            'old' => null,
            'new' => $model->total_amount,
            'type' => 'updated',
        ]);

        $this->assertFalse(isset($history->details['delivery_address.company']));
        $this->assertFalse(isset($history->details['delivery_address.save']));

        $this->assertEquals(14, count($history->details));
    }

    /** @test */
    public function success_add_delivery_type_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->delivery_address([])->draft(true)->create();

        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Pickup();

        $this->assertNull($model->delivery_address);
        $this->assertNotEquals($model->delivery_type, DeliveryType::Pickup());

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'delivery_type' => DeliveryType::Pickup(),
                ],
            ])
        ;

        $model->refresh();
        $this->assertEmpty($model->histories);
    }

    /** @test */
    public function fail_add_delivery_type_delivery_if_is_overload_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->weight(200)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_address([])
            ->draft(true)
            ->create();

        $this->itemBuilder
            ->order($model)
            ->inventory($inventory)
            ->create();

        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Delivery();

        $this->assertNull($model->delivery_type);

        $this->assertEmpty($model->histories);

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res, __("validation.custom.order.parts.has_overload"),'delivery_type');
    }

    /** @test */
    public function success_add_delivery_address_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Order */
        $model = $this->orderBuilder->delivery_address([])->draft(true)->create();

        $data = $this->data;
        $data['customer_id'] = $customer->id;
        $data['delivery_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
        ];

        $this->assertNull($model->delivery_address);
        $this->assertEmpty($customer->addresses);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'delivery_address' => [
                        'first_name' => $data['delivery_address']['first_name'],
                        'last_name' => $data['delivery_address']['last_name'],
                        'company' => $data['delivery_address']['company'],
                        'address' => $data['delivery_address']['address'],
                        'city' => $data['delivery_address']['city'],
                        'state' => $data['delivery_address']['state'],
                        'zip' => $data['delivery_address']['zip'],
                        'phone' => $data['delivery_address']['phone'],
                        'save' => false,
                        'customer_address_id' => null,
                    ],
                ],
            ])
        ;

        $customer->refresh();
        $this->assertEmpty($customer->addresses);
    }

    /** @test */
    public function success_add_customer_delivery_address_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $address Address */
        $address = $this->addressBuilder->customer($customer)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->delivery_address([])->draft(true)->create();

        $data = $this->data;
        $data['customer_id'] = $customer->id;
        $data['delivery_address'] = [
            'customer_address_id' => $address->id,
        ];

        $this->assertNull($model->delivery_address);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'delivery_address' => [
                        'first_name' => $address->first_name,
                        'last_name' => $address->last_name,
                        'company' => $address->company_name,
                        'address' => $address->address,
                        'city' => $address->city,
                        'state' => $address->state,
                        'zip' => $address->zip,
                        'phone' => $address->phone->getValue(),
                        'customer_address_id' => $address->id,
                        'save' => false,
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_delivery_address_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $data = $this->data;
        $data['customer_id'] = $customer->id;
        $data['delivery_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
            'save' => true,
        ];

        $this->assertNotEquals($model->delivery_address->first_name, $data['delivery_address']['first_name']);
        $this->assertNotEquals($model->delivery_address->last_name, $data['delivery_address']['last_name']);
        $this->assertNotEquals($model->delivery_address->last_name, $data['delivery_address']['last_name']);
        $this->assertNotEquals($model->delivery_address->company, $data['delivery_address']['company']);
        $this->assertNotEquals($model->delivery_address->city, $data['delivery_address']['city']);
        $this->assertNotEquals($model->delivery_address->state, $data['delivery_address']['state']);
        $this->assertNotEquals($model->delivery_address->zip, $data['delivery_address']['zip']);
        $this->assertNotEquals($model->delivery_address->phone, $data['delivery_address']['phone']);
        $this->assertEmpty($customer->addresses);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'delivery_address' => [
                        'first_name' => $data['delivery_address']['first_name'],
                        'last_name' => $data['delivery_address']['last_name'],
                        'company' => $data['delivery_address']['company'],
                        'address' => $data['delivery_address']['address'],
                        'city' => $data['delivery_address']['city'],
                        'state' => $data['delivery_address']['state'],
                        'zip' => $data['delivery_address']['zip'],
                        'phone' => $data['delivery_address']['phone'],
                        'save' => true,
                    ],
                ],
            ])
        ;

        $customer->refresh();
    }

    /** @test */
    public function success_update_customer_delivery_address_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        $address_1 = $this->addressBuilder->customer($customer)->create();
        /** @var $address_2 Address */
        $address_2 = $this->addressBuilder->customer($customer)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $data = $this->data;
        $data['customer_id'] = $customer->id;
        $data['delivery_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
            'save' => true,
            'customer_address_id' => $address_2->id,
        ];

        $this->assertNotEquals($model->delivery_address->first_name, $address_2->first_name);
        $this->assertNotEquals($model->delivery_address->last_name, $address_2->last_name);
        $this->assertNotEquals($model->delivery_address->company, $address_2->company_name);
        $this->assertNotEquals($model->delivery_address->city, $address_2->city);
        $this->assertNotEquals($model->delivery_address->state, $address_2->state);
        $this->assertNotEquals($model->delivery_address->zip, $address_2->zip);
        $this->assertNotEquals($model->delivery_address->phone, $address_2->phone->getValue());

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'delivery_address' => [
                        'first_name' => $address_2->first_name,
                        'last_name' => $address_2->last_name,
                        'company' => $address_2->company_name,
                        'address' => $address_2->address,
                        'city' => $address_2->city,
                        'state' => $address_2->state,
                        'zip' => $address_2->zip,
                        'phone' => $address_2->phone->getValue(),
                        'save' => false,
                        'customer_address_id' => $address_2->id,
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function success_add_billing_address_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->billing_address([])->draft(true)->create();

        $data = $this->data;
        $data['billing_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
        ];

        $this->assertNull($model->billing_address);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'billing_address' => [
                        'first_name' => $data['billing_address']['first_name'],
                        'last_name' => $data['billing_address']['last_name'],
                        'company' => $data['billing_address']['company'],
                        'address' => $data['billing_address']['address'],
                        'city' => $data['billing_address']['city'],
                        'state' => $data['billing_address']['state'],
                        'zip' => $data['billing_address']['zip'],
                        'phone' => $data['billing_address']['phone'],
                        'save' => false,
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function success_update_billing_address_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $data = $this->data;
        $data['billing_address'] = [
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
            'save' => true,
        ];

        $this->assertNotEquals($model->billing_address->first_name, $data['billing_address']['first_name']);
        $this->assertNotEquals($model->billing_address->last_name, $data['billing_address']['last_name']);
        $this->assertNotEquals($model->billing_address->last_name, $data['billing_address']['last_name']);
        $this->assertNotEquals($model->billing_address->company, $data['billing_address']['company']);
        $this->assertNotEquals($model->billing_address->city, $data['billing_address']['city']);
        $this->assertNotEquals($model->billing_address->state, $data['billing_address']['state']);
        $this->assertNotEquals($model->billing_address->zip, $data['billing_address']['zip']);
        $this->assertNotEquals($model->billing_address->phone, $data['billing_address']['phone']);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'billing_address' => [
                        'first_name' => $data['billing_address']['first_name'],
                        'last_name' => $data['billing_address']['last_name'],
                        'company' => $data['billing_address']['company'],
                        'address' => $data['billing_address']['address'],
                        'city' => $data['billing_address']['city'],
                        'state' => $data['billing_address']['state'],
                        'zip' => $data['billing_address']['zip'],
                        'phone' => $data['billing_address']['phone'],
                        'save' => false,
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function success_add_payments_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->payment_method(null)
            ->payment_terms(null)
            ->create();

        $data = $this->data;
        $data['payment'] = [
            'method' => PaymentMethod::Cash(),
            'terms' => PaymentTerms::Immediately(),
            'with_tax_exemption' => true,
        ];

        $this->assertNull($model->payment_method);
        $this->assertNull($model->payment_terms);
        $this->assertFalse($model->with_tax_exemption);

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'payment' => [
                        'method' => $data['payment']['method'],
                        'terms' => $data['payment']['terms'],
                        'with_tax_exemption' => $data['payment']['with_tax_exemption'],
                    ],
                ],
            ])
        ;

        $model->refresh();
        $this->assertEmpty($model->histories);

    }

    /** @test */
    public function success_update_payments_as_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->payment_method(null)
            ->payment_terms(null)
            ->create();

        $data = $this->data;
        $data['payment'] = [
            'method' => PaymentMethod::Cash(),
            'terms' => PaymentTerms::Immediately(),
            'with_tax_exemption' => true,
        ];

        $this->assertNotEquals($model->payment_method, $data['payment']['method']);
        $this->assertNull($model->payment_terms, $data['payment']['terms']);
        $this->assertFalse($model->with_tax_exemption, $data['payment']['with_tax_exemption']);

        $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'payment' => [
                        'method' => $data['payment']['method'],
                        'terms' => $data['payment']['terms'],
                        'with_tax_exemption' => $data['payment']['with_tax_exemption'],
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function fail_update_order_is_delivered()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->is_paid(false)
            ->status(OrderStatus::Delivered())
            ->create();

        $data = $this->fullData;

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.cant_edit"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_update_delivery_type_as_pickup_not_billing_address()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data = $this->fullData;
        unset($data['billing_address']);

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res,
            __('validation.required', ['attribute' => 'Billing Address']),
            'billing_address'
        );
    }


//    public function fail_update_delivery_type_as_delivery_not_delivery_address_customer_id()
//    {
//        $this->loginUserAsSuperAdmin();
//
//        /** @var $model Order */
//        $model = $this->orderBuilder
//            ->create();
//
//        $data = $this->fullData;
//        $data['delivery_type'] = DeliveryType::Delivery();
//        unset($data['delivery_address']);
//        $data['delivery_address']['first_name'] = 'fff';
//
////        dd($data);
//
//        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
//            ->dump()
//        ;
//
//        self::assertValidationMsg($res,
//            __('validation.required', ['attribute' => 'Delivery Address']),
//            'delivery_address'
//        );
//    }

    /** @test */
    public function fail_update_delivery_type_as_delivery_not_delivery_address()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $data = $this->fullData;
        $data['delivery_type'] = DeliveryType::Delivery();
        unset($data['delivery_address']);

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res,
            __('validation.required', ['attribute' => 'Delivery Address']),
            'delivery_address'
        );
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data['customer_id'] = null;

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.customer_id')]),
            'customer_id'
        );
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['customer_id', null, 'validation.required', ['attribute' => 'validation.attributes.customer_id']],
            ['customer_id', 99999, 'validation.exists', ['attribute' => 'validation.attributes.customer_id']],
            ['source', null, 'validation.required', ['attribute' => 'validation.attributes.source']],
            ['source', 'wrong', 'validation.in', ['attribute' => 'validation.attributes.source']],
        ];
    }

    /**
     * @dataProvider validateDeliveryAddress
     * @test
     */
    public function validate_delivery_address($field, $value, $msgKey, $attr)
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Delivery();
        $data['delivery_address'] = [
            'customer_address_id' => null,
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
        ];
        $data['delivery_address'][$field] = $value;

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res, __($msgKey, $attr), "delivery_address.{$field}");
    }

    public static function validateDeliveryAddress(): array
    {
        return [
            ['first_name', null, 'validation.required', ['attribute' => 'First name']],
            ['last_name', null, 'validation.required', ['attribute' => 'Last name']],
            ['address', null, 'validation.required', ['attribute' => 'Address']],
            ['city', null, 'validation.required', ['attribute' => 'City']],
            ['state', null, 'validation.required', ['attribute' => 'State']],
            ['zip', null, 'validation.required', ['attribute' => 'Zip']],
            ['phone', null, 'validation.required', ['attribute' => 'Phone']],
        ];
    }

    /**
     * @dataProvider validateBillingAddress
     * @test
     */
    public function validate_billing_address($field, $value, $msgKey, $attr)
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;
        $data['delivery_type'] = DeliveryType::Pickup();
        $data['billing_address'] = [
            'customer_address_id' => null,
            'first_name' => 'first',
            'last_name' => 'last',
            'address' => 'some address',
            'company' => 'company',
            'city' => 'Sacramento',
            'state' => 'CA',
            'zip' => '99808',
            'phone' => '9811111111',
        ];
        $data['billing_address'][$field] = $value;

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data)
        ;

        self::assertValidationMsg($res, __($msgKey, $attr), "billing_address.{$field}");
    }

    public static function validateBillingAddress(): array
    {
        return [
            ['first_name', null, 'validation.required', ['attribute' => 'First name']],
            ['last_name', null, 'validation.required', ['attribute' => 'Last name']],
            ['address', null, 'validation.required', ['attribute' => 'Address']],
            ['city', null, 'validation.required', ['attribute' => 'City']],
            ['state', null, 'validation.required', ['attribute' => 'State']],
            ['zip', null, 'validation.required', ['attribute' => 'Zip']],
            ['phone', null, 'validation.required', ['attribute' => 'Phone']],
        ];
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.parts.update', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
