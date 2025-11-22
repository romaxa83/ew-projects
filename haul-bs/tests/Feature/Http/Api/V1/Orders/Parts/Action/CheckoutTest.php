<?php

namespace Feature\Http\Api\V1\Orders\Parts\Action;

use App\Enums\Inventories\Transaction\OperationType;
use App\Enums\Orders\OrderType;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\History\Services\OrderPartsHistoryService;
use App\Models\Customers\Customer;
use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Order;
use App\Notifications\Orders\Parts\PaymentLink;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Orders\Parts\ShippingBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected UserBuilder $userBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected ItemBuilder $itemBuilder;
    protected ShippingBuilder $shippingBuilder;
    protected CustomerBuilder $customerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->shippingBuilder = resolve(ShippingBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    private function mockingPaymentService(): void
    {
        $mock = $this->createMock(PaymentService::class);
        $mock->expects($this->once())
            ->method('getPaymentLink')
            ->will($this->returnValue('http://localhost'));
        $this->app->instance(PaymentService::class, $mock);
    }

    /** @test */
    public function success_checkout()
    {
        Notification::fake();

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Delivery)
            ->draft(true)
            ->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $this->assertFalse(in_array($model->payment_method, PaymentMethod::forOnline()));

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::New(),
                    'sales_manager' => null,
                    'is_draft' => false
                ],
            ])
        ;

        Notification::assertNotSentTo(new AnonymousNotifiable(), PaymentLink::class);
    }

    /** @test */
    public function success_checkout_online_and_send_payment_link()
    {
        $this->mockingPaymentService();
        Notification::fake();

        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Delivery)
            ->draft(true)
            ->payment_method(PaymentMethod::Online())
            ->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $this->assertTrue(in_array($model->payment_method, PaymentMethod::forOnline()));

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::New(),
                    'sales_manager' => null,
                    'is_draft' => false
                ],
            ])
        ;

        Notification::assertSentTo(new AnonymousNotifiable(), PaymentLink::class,
            function ($notification, $channels, $notifiable) use ($model) {
                return $notifiable->routes['mail'] == $model->customer->email
                    && $notification->name == $model->customer->full_name
                    && $notification->link == 'http://localhost'
                    ;
            }
        );

        $model->refresh();

        $history = $model->histories->where('msg', OrderPartsHistoryService::HISTORY_MSG_ORDER_SEND_PAYMENT_LINK)->first();

        $this->assertEquals($history->type, HistoryType::ACTIVITY);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.parts.send_payment_link');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            "client_name" => $model->customer->full_name,
            "client_email" => $model->customer->email->getValue()
        ]);
        $this->assertEmpty($history->details);
    }

    /** @test */
    public function success_checkout_check_total_amount()
    {
        $this->loginUserAsSuperAdmin();

        $qty_1 = 4;
        $price_1 = 4.5;
        $qty_2 = 10;
        $price_2 = 5;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->delivery_type(DeliveryType::Delivery)
            ->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)
            ->qty($qty_1)
            ->price($price_1)
            ->create();
        $this->itemBuilder->order($model)
            ->qty($qty_2)
            ->price($price_2)
            ->create();

        $this->assertFalse($model->isPaid());
        $this->assertNull($model->paid_at);
        $this->assertNull($model->total_amount);
        $this->assertNull($model->paid_amount);
        $this->assertNull($model->debt_amount);

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;

        $model->refresh();

        $this->assertFalse($model->isPaid());
        $this->assertNull($model->paid_at);
        $this->assertEquals($model->total_amount, ($qty_1*$price_1) + ($qty_2*$price_2));
        $this->assertEquals($model->debt_amount, ($qty_1*$price_1) + ($qty_2*$price_2));
        $this->assertEquals($model->paid_amount, 0);
    }

    /** @test */
    public function success_checkout_as_sales_manager()
    {
        $user = $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Delivery)
            ->draft(true)
            ->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::In_process(),
                    'sales_manager' => [
                        'id' => $user->id
                    ],
                    'is_draft' => false
                ],
            ])
        ;

        $model = $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->details['sales_manager_id'], [
            'old' => null,
            'new' => $user->full_name,
            'type' => 'added',
        ]);
    }

    /** @test */
    public function success_checkout_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Delivery)
            ->draft(true)
            ->create();

        $shipping_1 = $this->shippingBuilder->order($model)->terms('terms')->create();
        $shipping_2 = $this->shippingBuilder->order($model)->create();

        $item_1 = $this->itemBuilder->inventory($inventory_1)->order($model)->shipping($shipping_1)->create();
        $item_2 = $this->itemBuilder->inventory($inventory_2)->order($model)->shipping($shipping_2)->create();

        $this->assertEmpty($model->histories);

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::New(),
                    'sales_manager' => null,
                    'is_draft' => false
                ],
            ])
        ;

        $model = $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.created');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details['customer_id'], [
            'old' => null,
            'new' => $model->customer->full_name,
            'type' => 'added',
        ]);
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
            'new' => $model->delivery_address->phone,
            'type' => 'added',
        ]);

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
            'new' => $model->billing_address->phone,
            'type' => 'added',
        ]);

//        $this->assertEquals($history->details['shipping_method.'. $shipping_1->id .'.method'], [
//            'old' => null,
//            'new' => $shipping_1->method->value,
//            'type' => 'added',
//        ]);
//        $this->assertEquals($history->details['shipping_method.'. $shipping_1->id .'.cost'], [
//            'old' => null,
//            'new' => $shipping_1->cost,
//            'type' => 'added',
//        ]);
//        $this->assertEquals($history->details['shipping_method.'. $shipping_1->id .'.terms'], [
//            'old' => null,
//            'new' => $shipping_1->terms,
//            'type' => 'added',
//        ]);
//        $this->assertEquals($history->details['shipping_method.'. $shipping_2->id .'.method'], [
//            'old' => null,
//            'new' => $shipping_2->method->value,
//            'type' => 'added',
//        ]);
//        $this->assertEquals($history->details['shipping_method.'. $shipping_2->id .'.cost'], [
//            'old' => null,
//            'new' => $shipping_2->cost,
//            'type' => 'added',
//        ]);

        $this->assertEquals($history->details['payment_method'], [
            'old' => null,
            'new' => $model->payment_method->value,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['payment_terms'], [
            'old' => null,
            'new' => $model->payment_terms->value,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['with_tax_exemption'], [
            'old' => null,
            'new' => $model->with_tax_exemption,
            'type' => 'added',
        ]);

        $this->assertEquals($history->details['status'], [
            'old' => null,
            'new' => OrderStatus::New(),
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['source'], [
            'old' => null,
            'new' => OrderSource::BS(),
            'type' => 'added',
        ]);

        $this->assertEquals($history->details['items.'. $item_1->id . '.inventories.' . $inventory_1->id .'.name'], [
            'old' => null,
            'new' => $inventory_1->name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['items.'. $item_1->id . '.inventories.' . $inventory_1->id .'.quantity'], [
            'old' => null,
            'new' => $item_1->qty,
            'type' => 'added',
        ]);

        $this->assertEquals($history->details['items.'. $item_2->id . '.inventories.' . $inventory_2->id .'.name'], [
            'old' => null,
            'new' => $inventory_2->name,
            'type' => 'added',
        ]);
        $this->assertEquals($history->details['items.'. $item_2->id . '.inventories.' . $inventory_2->id .'.quantity'], [
            'old' => null,
            'new' => $item_2->qty,
            'type' => 'added',
        ]);
    }

    /** @test */
    public function success_checkout_not_billing_address()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Delivery)
            ->draft(true)
            ->billing_address([])
            ->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $this->assertEmpty($model->billing_address);

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::New(),
                    'sales_manager' => null,
                    'is_draft' => false,
                    'billing_address' => [
                        'first_name' => $model->delivery_address->first_name,
                        'last_name' => $model->delivery_address->last_name,
                        'company' => $model->delivery_address->company,
                        'address' => $model->delivery_address->address,
                        'city' => $model->delivery_address->city,
                        'state' => $model->delivery_address->state,
                        'zip' => $model->delivery_address->zip,
                        'phone' => $model->delivery_address->phone,
                    ]
                ],
            ])
        ;
    }

    /** @test */
    public function success_checkout_not_delivery_address()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Pickup)
            ->draft(true)
            ->delivery_address([])
            ->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $this->assertEmpty($model->delivery_address);

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'delivery_address' => null
                ],
            ])
        ;
    }

    /** @test */
    public function success_checkout_save_address_to_customer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->customer($customer)
            ->delivery_type(DeliveryType::Pickup)
            ->create();
        $model->delivery_address->save = true;
        $model->save();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $this->assertTrue($model->delivery_address->save);
        $this->assertEmpty($customer->addresses);

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::New(),
                    'sales_manager' => null,
                    'is_draft' => false,
                ],
            ])
        ;

        $customer->refresh();

        $address = $customer->addresses[0];

        $this->assertEquals($address->first_name, $model->delivery_address->first_name);
        $this->assertEquals($address->last_name, $model->delivery_address->last_name);
        $this->assertEquals($address->company_name, $model->delivery_address->company);
        $this->assertEquals($address->address, $model->delivery_address->address);
        $this->assertEquals($address->city, $model->delivery_address->city);
        $this->assertEquals($address->state, $model->delivery_address->state);
        $this->assertEquals($address->zip, $model->delivery_address->zip);
        $this->assertEquals($address->phone, $model->delivery_address->phone);
    }

    /** @test */
    public function success_check_reserve_for_order()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $wasQty = 10;
        $reserveQty = 4;

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->customer($customer)
            ->delivery_type(DeliveryType::Pickup)
            ->create();
        $model->save();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->quantity($wasQty)->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->inventory($inventory)->qty($reserveQty)->create();

        $this->assertEmpty($inventory->transactions);
        $this->assertEmpty($inventory->histories);

        $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => OrderStatus::New(),
                    'sales_manager' => null,
                    'is_draft' => false,
                ],
            ])
        ;

        $model->refresh();
        $inventory->refresh();

        $this->assertCount(1, $inventory->transactions);

        $this->assertNull($inventory->transactions[0]->order_id);
        $this->assertEquals($inventory->transactions[0]->inventory_id, $inventory->id);
        $this->assertEquals($inventory->transactions[0]->operation_type->value, OperationType::SOLD->value);
        $this->assertEquals($inventory->transactions[0]->invoice_number, $model->order_number);
        $this->assertEquals($inventory->transactions[0]->price, $inventory->price_retail);
        $this->assertEquals($inventory->transactions[0]->quantity, $reserveQty);
        $this->assertEquals($inventory->transactions[0]->order_parts_id, $model->id);
        $this->assertEquals($inventory->transactions[0]->order_type, OrderType::Parts);
        $this->assertTrue($inventory->transactions[0]->is_reserve);
        $this->assertNull($inventory->transactions[0]->order_id);

        $this->assertEquals($inventory->quantity, $wasQty - $reserveQty);

        $this->assertCount(1, $inventory->histories);

        $this->assertEquals($inventory->transactions[0]->order_parts_id, $model->id);

        /** @var $history History */
        $history = $inventory->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.inventory.quantity_reserved_for_order');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $inventory->stock_number,
            'inventory_name' => $inventory->name,
            'user_id' => $user->id,
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
    public function fail_not_delivery_type()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->create();

        $this->assertNull($model->delivery_type);

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.parts.must_have_delivery_type'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_delivery_address()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Delivery)
            ->delivery_address([])
            ->draft(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.parts.must_have_delivery_address'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_billing_address_if_delivery_type_as_pickup()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->delivery_type(DeliveryType::Pickup)
            ->billing_address([])
            ->draft(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.parts.must_have_billing_address'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_items()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->delivery_type(DeliveryType::Pickup)->create();

        $this->shippingBuilder->order($model)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.parts.must_have_items'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_payment_methods()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->draft(true)
            ->delivery_type(DeliveryType::Pickup)
            ->payment_method(null)
            ->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.parts.must_have_payment_methods'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_overload_and_delivery_method_delivery()
    {
        $this->loginUserAsSuperAdmin();

        $inventory = $this->inventoryBuilder->weight(200)->create();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->delivery_type(DeliveryType::Delivery)->create();

        $this->itemBuilder->order($model)->inventory($inventory)->create();
        $this->shippingBuilder->order($model)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __("validation.custom.order.parts.has_overload"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_draft()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->shippingBuilder->order($model)->create();
        $this->itemBuilder->order($model)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __('exceptions.orders.parts.must_be_draft'), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id + 1]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->draft(true)->create();

        $res = $this->postJson(route('api.v1.orders.parts.checkout', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
