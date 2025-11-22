<?php

namespace Feature\Http\Api\V1\Orders\Parts\Crud;

use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Entities\Locations\AddressEntity;
use App\Models\Inventories\Inventory;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Orders\Parts\PaymentBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected CustomerBuilder $customerBuilder;
    protected UserBuilder $userBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected ItemBuilder $itemBuilder;
    protected PaymentBuilder $paymentBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        $customer = $this->customerBuilder->create();

        $sales = $this->userBuilder->asSalesManager()->create();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->customer($customer)
            ->ecommerce_client()
            ->create();
        /** @var $payment Payment */
        $payment = $this->paymentBuilder->order($model)->create();

        $item = $this->itemBuilder
            ->order($model)
            ->inventory($inventory)
            ->free_shipping(true)
            ->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    'sales_manager' => [
                        'first_name',
                        'last_name',
                        'full_name',
                        'email',
                        'phone',
                        'phone_extension',
                        'phones',
                        'status',
                        'deleted_at',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'order_number' => $model->order_number,
                    'customer' => [
                        'id' => $customer->id
                    ],
                    'sales_manager' => [
                        'id' => $sales->id
                    ],
                    'status' => $model->status->value,
                    'paid_at' => $model->paid_at?->timestamp,
                    'status_changed_at' => $model->status_changed_at?->timestamp,
                    'items' => [
                        [
                            'id' => $item->id,
                            'quantity' => $item->qty,
                            'inventory' => [
                                'id' => $inventory->id,
                                'name' => $inventory->name,
                                'stock_number' => $inventory->stock_number,
                                'article_number' => $inventory->article_number,
                                'price' => $inventory->price_retail,
                                'old_price' => $inventory->old_price,
                                'discount' => $inventory->discount,
                                'quantity' => $inventory->quantity,
                                'main_image' => $inventory->getMainImg(),
                            ],
                        ]
                    ],
                    'payments' => [
                        [
                            'id' => $payment->id,
                            'amount' => $payment->amount,
                            'payment_date' => $payment->payment_at->timestamp,
                            'payment_method' => $payment->payment_method->value,
                            'payment_method_name' => $payment->payment_method->label(),
                            'notes' => $payment->notes,
                        ]
                    ],
                    'source' => $model->source->value,
                    'has_free_shipping_inventory' => true,
                    'has_paid_shipping_inventory' => false,
                    'inventory_amount' => $model->getTotalOnlyItems(),
                    'tax_amount' => $model->getTax(),
                    'total_amount' => $model->getAmount(),
                    'subtotal_amount' => $model->getSubtotal(),
                    'saving_amount' => $model->getSavingAmount(),
                    'ecommerce_client' => [
                        'first_name' => $model->ecommerce_client->first_name,
                        'last_name' => $model->ecommerce_client->last_name,
                        'email' => $model->ecommerce_client->email->getValue(),
                    ],
                    'delivery_cost' => $model->delivery_cost,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_with_amount()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->billing_address(AddressEntity::make([
                'first_name' => 'Valerie',
                'last_name' => 'Schinner',
                'company' => 'Haag-Johns',
                'address' => '34295 Gabe Turnpike',
                'city' => 'East Ruthside',
                'state' => 'TX',
                'zip' => '60001',
                'phone' => '1324234234',
            ]))
            ->create();

        $item_1 = $this->itemBuilder
            ->order($model)
            ->price(14.5)
            ->qty(3)
            ->create();
        $item_2 = $this->itemBuilder
            ->order($model)
            ->price(10)
            ->qty(5)
            ->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'inventory_amount' => 93.5,
                    'tax_amount' => 9.82,
                    'total_amount' => 103.32,
                    'ecommerce_client' => null,
                ],
            ])
        ;
    }


    /** @test */
    public function success_show_has_paid_item()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder
            ->order($model)
            ->inventory($inventory)
            ->free_shipping(false)
            ->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'has_free_shipping_inventory' => false,
                    'has_paid_shipping_inventory' => true,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_paid_and_free_item()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder->create();
        $inventory_1 = $this->inventoryBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->itemBuilder
            ->order($model)
            ->inventory($inventory)
            ->free_shipping(false)
            ->create();
        $this->itemBuilder
            ->order($model)
            ->inventory($inventory_1)
            ->free_shipping(true)
            ->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'has_free_shipping_inventory' => true,
                    'has_paid_shipping_inventory' => true,
                ],
            ])
        ;
    }

    /** @test */
    public function success_view_if_sales_owner()
    {
        $sales = $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($sales)
            ->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;
    }

    /** @test */
    public function success_view_if_not_sales()
    {
        $sales = $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
        ;
    }

    /** @test */
    public function success_view_scope()
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'action_scopes' => [
                        'can_update' => $model->canUpdate(),
                        'can_add_payment' => $model->canAddPayment(),
                        'can_delete' => $model->canDelete(),
                        'can_send_invoice' => $model->canSendInvoice(),
                        'can_refunded' => $model->canRefunded(),
                        'can_change_status' => $model->canChangeStatus(),
                        'can_canceled' => $model->canCanceled(),
                        'can_assign_manager' => $model->canAssignManger(),
                    ],
                ],
            ])
        ;
    }

    /** @test */
    public function success_view_if_has_overload()
    {
        $this->loginUserAsSalesManager();

        $inventory_1 = $this->inventoryBuilder->weight(100)->create();
        $inventory_2 = $this->inventoryBuilder->weight(200)->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->create();

        $this->itemBuilder->order($model)->inventory($inventory_1)->create();
        $this->itemBuilder->order($model)->inventory($inventory_2)->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'has_overload_inventory' => true,
                ],
            ])
        ;
    }

    /**
     * @dataProvider statusCanRefunded
     * @test
     */
    public function success_view_if_can_refunded($status)
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->is_paid(true)->create();


        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'action_scopes' => [
                        'can_refunded' => true,
                    ]
                ],
            ])
        ;
    }

    public static function statusCanRefunded(): array
    {
        return [
            [OrderStatus::Damaged()],
            [OrderStatus::Lost()],
            [OrderStatus::Returned()],
            [OrderStatus::Canceled()],
        ];
    }

    /**
     * @dataProvider statusCantRefunded
     * @test
     */
    public function success_view_if_cant_refunded($status)
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'action_scopes' => [
                        'can_refunded' => false,
                    ]
                ],
            ])
        ;
    }

    public static function statusCantRefunded(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
        ];
    }

    /**
     * @dataProvider statusCanChangeStatus
     * @test
     */
    public function field_can_change_status_as_true($status)
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'action_scopes' => [
                        'can_change_status' => true,
                    ]
                ],
            ])
        ;
    }

    public static function statusCanChangeStatus(): array
    {
        return [
            [OrderStatus::In_process()],
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
        ];
    }

    /**
     * @dataProvider statusCantChangeStatus
     * @test
     */
    public function field_can_change_status_as_false($status)
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'action_scopes' => [
                        'can_change_status' => false,
                    ]
                ],
            ])
        ;
    }

    public static function statusCantChangeStatus(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }

    /**
     * @dataProvider statusCanCanceled
     * @test
     */
    public function field_can_canceled_as_true($status)
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'action_scopes' => [
                        'can_canceled' => true,
                    ]
                ],
            ])
        ;
    }

    public static function statusCanCanceled(): array
    {
        return [
            [OrderStatus::New()],
            [OrderStatus::In_process()],
        ];
    }

    /**
     * @dataProvider statusCantCanceled
     * @test
     */
    public function field_can_canceled_as_false($status)
    {
        $this->loginUserAsSalesManager();

        /** @var $model Order */
        $model = $this->orderBuilder->status($status)->create();

        $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'action_scopes' => [
                        'can_canceled' => false,
                    ]
                ],
            ])
        ;
    }

    public static function statusCantCanceled(): array
    {
        return [
            [OrderStatus::Sent()],
            [OrderStatus::Pending_pickup()],
            [OrderStatus::Delivered()],
            [OrderStatus::Canceled()],
            [OrderStatus::Returned()],
            [OrderStatus::Lost()],
            [OrderStatus::Damaged()],
        ];
    }

    /** @test */
    public function fail_another_sales()
    {
        $this->loginUserAsSalesManager();

        $anotherSales = $this->userBuilder->asSalesManager()->create();

        /** @var $model Order */
        $model = $this->orderBuilder
            ->sales_manager($anotherSales)
            ->create();

        $res = $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.orders.parts.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
