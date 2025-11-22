<?php

namespace Tests\Feature\Http\Api\V1\Orders\Parts\Crud;

use App\Enums\Orders\Parts\OrderPaymentStatus;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Shipping;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\Parts\DeliveryBuilder;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Orders\Parts\ShippingBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected CustomerBuilder $customerBuilder;
    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected ItemBuilder $itemBuilder;
    protected DeliveryBuilder $deliveryBuilder;
    protected ShippingBuilder $shippingBuilder;
    protected CommentBuilder $commentBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->deliveryBuilder = resolve(DeliveryBuilder::class);
        $this->shippingBuilder = resolve(ShippingBuilder::class);
        $this->commentBuilder = resolve(CommentBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now()->subDay();

        $m_1 = $this->orderBuilder->setDate('created_at', $now->addHours(4))->ecommerce_client()->create();
        $m_2 = $this->orderBuilder->setDate('created_at', $now->addHours(3))->ecommerce_client()->create();
        $m_3 = $this->orderBuilder->setDate('created_at', $now->addHours(2))->ecommerce_client()->create();
        $this->orderBuilder->deleted()->create();
        $this->orderBuilder->draft(true)->create();

        $this->itemBuilder->order($m_1)->create();

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'order_number',
                        'customer' => [
                            'id',
                            'first_name',
                            'last_name',
                            'phone',
                            'phone_extension',
                            'email',
                        ],
                        'status',
                        'total_amount',
                        'paid_amount',
                        'paid_at',
                        'refunded_at',
                        'source',
                        'is_overdue',
                        'overdue_days',
                        'items_count',
                        'comments_count',
                        'delivery_full_address',
                        'delivery_phone',
                        'shipping',
                        'delivery',
                        'delivery_type',
                        'is_refunded',
                        'action_scopes' => [
                            'can_update',
                            'can_add_payment',
                            'can_delete',
                            'can_send_invoice',
                            'can_refunded',
                            'can_change_status',
                            'can_canceled',
                        ],
                        'ecommerce_client' => [
                            'first_name',
                            'last_name',
                            'email',
                        ],
                        'items' => [
                            [
                                'id',
                                'quantity',
                                'free_shipping',
                                'price',
                                'price_old',
                                'delivery_cost',
                                'is_overload',
                                'is_overload',
                                'inventory' => [
                                    'id',
                                    'name',
                                    'stock_number',
                                    'article_number',
                                    'price',
                                    'old_price',
                                    'discount',
                                    'quantity',
                                    'main_image',
                                ]
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'to' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->orderBuilder->create();
        $this->orderBuilder->create();
        $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.parts', ['page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'total' => 3,
                    'to' => null,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_per_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->orderBuilder->create();
        $this->orderBuilder->create();
        $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.parts', ['per_page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'per_page' => 2,
                    'to' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 0,
                    'to' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_view_comment_counts()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        CarbonImmutable::setTestNow($now->addDays(20));

        $model = $this->orderBuilder
            ->is_paid(false)
            ->setDate('past_due_at', $now)
            ->create();

        $this->commentBuilder->model($model)->create();
        $this->commentBuilder->model($model)->create();

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $model->id,
                        'comments_count' => 2,
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_view_is_overdue_not_paid()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        CarbonImmutable::setTestNow($now->addDays(20));

        $model = $this->orderBuilder
            ->is_paid(false)
            ->setDate('past_due_at', $now)
            ->create();

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $model->id,
                        'is_overdue' => true,
                        'overdue_days' => 20,
                        'comments_count' => 0,
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_view_is_overdue_is_paid()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        CarbonImmutable::setTestNow($now->addDays(20));

        $model = $this->orderBuilder
            ->is_paid(true)
            ->setDate('past_due_at', $now)
            ->create();

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $model->id,
                        'is_overdue' => false,
                        'overdue_days' => 0,
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_as_sales_manager()
    {
        $sales = $this->loginUserAsSalesManager();

        $now = CarbonImmutable::now()->subDay();

        $anotherSales = $this->userBuilder->asSalesManager()->create();

        $m_1 = $this->orderBuilder->sales_manager($sales)->setDate('created_at', $now->addHours(4))->create();
        $m_2 = $this->orderBuilder->sales_manager($anotherSales)->setDate('created_at', $now->addHours(3))->create();
        $m_3 = $this->orderBuilder->setDate('created_at', $now->addHours(2))->create();
        $this->orderBuilder->deleted()->create();

        $this->assertNull($m_3->sales_manager_id);

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 2,
                    'to' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_order_number_and_view_data()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Order */
        $m_1 = $this->orderBuilder->order_number('haulk')->create();
        $m_2 = $this->orderBuilder->order_number('aaaaa')->create();
        $m_3 = $this->orderBuilder->order_number('bbbbbb')->create();

        $this->itemBuilder->order($m_1)->create();
        $this->itemBuilder->order($m_1)->create();

        /** @var $delivery Delivery */
        $delivery = $this->deliveryBuilder->order($m_1)->create();

        /** @var $shipping Shipping */
        $shipping = $this->shippingBuilder->order($m_1)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'search' => 'haul'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'items_count' => 2,
                        'delivery_phone' => $m_1->delivery_address->phone->getValue(),
                        'delivery_type' => $m_1->delivery_type?->value,
                        'delivery_full_address' => $m_1->delivery_address->getFullAddress(),
                        'shipping' => [
                            "name" => $shipping->method->value,
                            "cost" => $shipping->cost,
                            "terms" => $shipping->terms,
                        ],
                        'delivery' => [
                            [
                                'id' => $delivery->id,
                                'delivery_method' => $delivery->method->value,
                                'delivery_cost' => $delivery->cost,
                                'tracking_number' => $delivery->tracking_number,
                            ]
                        ]
                    ],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_sales_manager()
    {
        $this->loginUserAsSuperAdmin();

        $sales_1 = $this->userBuilder->asSalesManager()->create();
        $sales_2 = $this->userBuilder->asSalesManager()->create();
        $sales_3 = $this->userBuilder->asSalesManager()->create();

        $m_1 = $this->orderBuilder->sales_manager($sales_1)->create();
        $m_2 = $this->orderBuilder->sales_manager($sales_2)->create();
        $m_3 = $this->orderBuilder->sales_manager($sales_3)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'sales_manager_id' => $sales_2->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_sales_manager_as_sales_manager()
    {
        $sales_1 = $this->loginUserAsSalesManager();

        $sales_2 = $this->userBuilder->asSalesManager()->create();
        $sales_3 = $this->userBuilder->asSalesManager()->create();

        $m_1 = $this->orderBuilder->sales_manager($sales_1)->create();
        $m_2 = $this->orderBuilder->sales_manager($sales_2)->create();
        $m_3 = $this->orderBuilder->sales_manager($sales_3)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'sales_manager_id' => $sales_2->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_sales_manager_as_sales_manager_filter()
    {
        $sales_1 = $this->loginUserAsSalesManager();

        $sales_2 = $this->userBuilder->asSalesManager()->create();
        $sales_3 = $this->userBuilder->asSalesManager()->create();

        $m_1 = $this->orderBuilder->sales_manager($sales_1)->create();
        $m_2 = $this->orderBuilder->sales_manager($sales_2)->create();
        $m_3 = $this->orderBuilder->sales_manager($sales_3)->create();
        $m_4 = $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.parts', [
            'sales_manager_id' => $sales_1->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_status()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now()->subDay();

        $m_1 = $this->orderBuilder->status(OrderStatus::New())->setDate('created_at', $now->addHours(4))->create();
        $m_2 = $this->orderBuilder->status(OrderStatus::New())->setDate('created_at', $now->addHours(3))->create();
        $m_3 = $this->orderBuilder->status(OrderStatus::Sent())->create();

        $this->getJson(route('api.v1.orders.parts', [
            'status' => OrderStatus::New()
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_status_as_canceled()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->status(OrderStatus::New())->create();
        $m_2 = $this->orderBuilder->status(OrderStatus::New())->create();
        $m_3 = $this->orderBuilder->status(OrderStatus::Canceled())->create();

        $this->getJson(route('api.v1.orders.parts', [
            'status' => OrderStatus::Canceled()
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_source()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->source(OrderSource::BS)->create();
        $m_2 = $this->orderBuilder->source(OrderSource::Amazon)->create();
        $m_3 = $this->orderBuilder->source(OrderSource::Amazon)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'source' => OrderSource::BS()
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_as_paid()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->is_paid(true)->create();
        $m_2 = $this->orderBuilder->is_paid(false)->create();
        $m_3 = $this->orderBuilder->is_paid(false)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'payment_status' => OrderPaymentStatus::Paid
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_as_not_paid()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now()->subDay();

        $m_1 = $this->orderBuilder->is_paid(true)->setDate('created_at', $now->addHours(4))->create();
        $m_2 = $this->orderBuilder->is_paid(false)->setDate('created_at', $now->addHours(3))->create();
        $m_3 = $this->orderBuilder->is_paid(false)->setDate('created_at', $now->addHours(2))->create();

        $this->getJson(route('api.v1.orders.parts', [
            'payment_status' => OrderPaymentStatus::Not_paid
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_as_refunded()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->is_paid(true)->create();
        $m_2 = $this->orderBuilder->is_paid(false)->create();
        $m_3 = $this->orderBuilder->refunded_at()->create();

        $this->getJson(route('api.v1.orders.parts', [
            'payment_status' => OrderPaymentStatus::Refunded
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_inventory_name_and_stock_number()
    {
        $now = CarbonImmutable::now()->subDay();

        $this->loginUserAsSuperAdmin();

        $i_1 = $this->inventoryBuilder->name('haulktes')->stock_number('aaaaa1')->create();
        $i_2 = $this->inventoryBuilder->name('tes')->stock_number('aaaaa')->create();
        $i_3 = $this->inventoryBuilder->name('tesrrr')->stock_number('haulkaaaaa')->create();

        $m_1 = $this->orderBuilder->setDate('created_at', $now->addHours(4))->create();
        $m_2 = $this->orderBuilder->setDate('created_at', $now->addHours(3))->create();
        $m_3 = $this->orderBuilder->setDate('created_at', $now->addHours(2))->create();

        $this->itemBuilder->inventory($i_1)->order($m_1)->create();
        $this->itemBuilder->inventory($i_2)->order($m_2)->create();
        $this->itemBuilder->inventory($i_3)->order($m_3)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'search_inventory' => 'haul'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_inventory_article_number()
    {
        $this->loginUserAsSuperAdmin();

        $i_1 = $this->inventoryBuilder->name('sssss')->article_number('haul1')->create();
        $i_2 = $this->inventoryBuilder->name('tes')->article_number('aaaaa')->create();
        $i_3 = $this->inventoryBuilder->name('tesrrr')->article_number('bbbbb')->create();

        $m_1 = $this->orderBuilder->create();
        $m_2 = $this->orderBuilder->create();
        $m_3 = $this->orderBuilder->create();

        $this->itemBuilder->inventory($i_1)->order($m_1)->create();
        $this->itemBuilder->inventory($i_2)->order($m_2)->create();
        $this->itemBuilder->inventory($i_3)->order($m_3)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'search_inventory' => 'haul'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_customer_name()
    {
        $now = CarbonImmutable::now()->subDay();

        $this->loginUserAsSuperAdmin();

        $c_1 = $this->customerBuilder->first_name('aaaaaa')->last_name('zzzzz')->create();
        $c_2 = $this->customerBuilder->first_name('bbbbbb')->last_name('zzzzz')->create();
        $c_3 = $this->customerBuilder->first_name('zzzzzz')->last_name('aaaaa')->create();

        $m_1 = $this->orderBuilder->customer($c_1)->setDate('created_at', $now->addHours(4))->create();
        $m_2 = $this->orderBuilder->customer($c_2)->setDate('created_at', $now->addHours(3))->create();
        $m_3 = $this->orderBuilder->customer($c_3)->setDate('created_at', $now->addHours(2))->create();

        $this->getJson(route('api.v1.orders.parts', [
            'search_customer' => 'aaaaa'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_customer_email()
    {
        $this->loginUserAsSuperAdmin();

        $c_1 = $this->customerBuilder->email('aaaaaa@test.com')->create();
        $c_2 = $this->customerBuilder->email('bbbbbb@test.com')->create();
        $c_3 = $this->customerBuilder->email('cccccc@test.com')->create();

        $m_1 = $this->orderBuilder->customer($c_1)->create();
        $m_2 = $this->orderBuilder->customer($c_2)->create();
        $m_3 = $this->orderBuilder->customer($c_3)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'search_customer' => 'aaaaa'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_ecommerce_client_email()
    {
        $this->loginUserAsSuperAdmin();

        $c_1 = $this->customerBuilder->email('aaaaaa@test.com')->create();
        $c_2 = $this->customerBuilder->email('bbbbbb@test.com')->create();
        $c_3 = $this->customerBuilder->email('cccccc@test.com')->create();

        $m_1 = $this->orderBuilder->customer(null)->ecommerce_client([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
        ])->create();
        $m_2 = $this->orderBuilder->customer($c_2)->create();
        $m_3 = $this->orderBuilder->customer($c_3)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'search_customer' => 'john@'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_ecommerce_client_name()
    {
        $this->loginUserAsSuperAdmin();

        $c_2 = $this->customerBuilder
            ->first_name('aaaaaa')
            ->last_name('aaaaaa')
            ->email('bbbbbb@test.com')->create();
        $c_3 = $this->customerBuilder
            ->first_name('bbbbb')
            ->last_name('bbbbb')
            ->email('cccccc@test.com')->create();

        $m_1 = $this->orderBuilder->customer(null)->ecommerce_client([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
        ])->create();
        $m_2 = $this->orderBuilder->customer($c_2)->create();
        $m_3 = $this->orderBuilder->customer($c_3)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'search_customer' => 'doe'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_from()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $m_1 = $this->orderBuilder->created($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->created($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->created($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->created($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.parts', [
            'date_from' => $now->format('Y-m-d H:i')
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                    ['id' => $m_4->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_to()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $m_1 = $this->orderBuilder->created($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->created($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->created($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->created($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.parts', [
            'date_to' => $now->format('Y-m-d H:i')
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id,],
                    ['id' => $m_1->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_from_and_to()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $m_1 = $this->orderBuilder->created($now->subDays(4))->create();
        $m_2 = $this->orderBuilder->created($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->created($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->created($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.parts', [
            'date_from' => $now->subDays(3)->format('Y-m-d H:i'),
            'date_to' => $now->format('Y-m-d H:i')
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_date_from_and_to_and_email()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $customer_1 = $this->customerBuilder->first_name('aaaaaa')
            ->last_name('aaaaaa')->email('aaaa@gmail.com')->create();
        $customer_2 = $this->customerBuilder->first_name('bbbbb')
            ->last_name('bbbbb')->email('bbbbb@gmail.com')->create();
        $customer_3 = $this->customerBuilder->first_name('cccccc')
            ->last_name('ccccc')->email('ccccc@gmail.com')->create();
        $customer_4 = $this->customerBuilder->first_name('bbbbb')
            ->last_name('eeeee')->email('eeeee@gmail.com')->create();
        $customer_5 = $this->customerBuilder->first_name('jjjjjj')
            ->last_name('bbbbb')->email('jjjjj@gmail.com')->create();
        $customer_6 = $this->customerBuilder->first_name('dddddd')
            ->last_name('dddddd')->email('ddddd@gmail.com')->create();


        $m_1 = $this->orderBuilder->created($now->subDays(4))->customer($customer_1)->create();
        //check
        $m_2 = $this->orderBuilder->created($now->subDays(1))->customer($customer_2)->create();
        $m_3 = $this->orderBuilder->created($now->subDays(1))->customer($customer_3)->create();
        //check
        $m_4 = $this->orderBuilder->created($now->subDays(2))->customer($customer_4)->create();
        $m_5 = $this->orderBuilder->created($now->addDays(5))->customer($customer_5)->create();
        $m_6 = $this->orderBuilder->created($now->addDays(2))->customer($customer_6)->create();

        $this->getJson(route('api.v1.orders.parts', [
            'date_from' => $now->subDays(3)->format('Y-m-d H:i'),
            'date_to' => $now->format('Y-m-d H:i'),
            'search_customer' => 'bbbbb'
        ]))
            ->assertJson([
                'data' => [
//                    ['id' => $m_2->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_default_sort()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now()->subDays(2);

        $m_1 = $this->orderBuilder->status(OrderStatus::New())->setDate('created_at', $now->addHours(11))->create();
        $m_2 = $this->orderBuilder->status(OrderStatus::New())->setDate('created_at', $now->addHours(9))->create();
        $m_3 = $this->orderBuilder->status(OrderStatus::In_process())->setDate('created_at', $now->addHours(10))->create();
        $m_4 = $this->orderBuilder->status(OrderStatus::In_process())->setDate('created_at', $now->addHours(13))->create();
        $m_5 = $this->orderBuilder->status(OrderStatus::In_process())->setDate('created_at', $now->addHours(8))->create();

        $this->orderBuilder->deleted()->create();
        $this->orderBuilder->draft(true)->create();

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                    ['id' => $m_4->id],
                    ['id' => $m_3->id],
                    ['id' => $m_5->id],
                ],
                'meta' => [
                    'total' => 5,
                ]
            ])
        ;
    }

    /** @test */
    public function success_default_sort_by_statuses_and_without_canceled()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->status(OrderStatus::In_process())->create();
        $m_2 = $this->orderBuilder->status(OrderStatus::New())->create();
        $m_3 = $this->orderBuilder->status(OrderStatus::Damaged())->create();
        $m_4 = $this->orderBuilder->status(OrderStatus::Sent())->create();
        $m_5 = $this->orderBuilder->status(OrderStatus::Pending_pickup())->create();
        $m_6 = $this->orderBuilder->status(OrderStatus::Lost())->create();
        $m_7 = $this->orderBuilder->status(OrderStatus::Returned())->create();
        $m_8 = $this->orderBuilder->status(OrderStatus::Canceled())->create();
        $m_9 = $this->orderBuilder->status(OrderStatus::Delivered())->create();

        $this->orderBuilder->deleted()->create();
        $this->orderBuilder->draft(true)->create();

        $this->getJson(route('api.v1.orders.parts'))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                    ['id' => $m_4->id],
                    ['id' => $m_5->id],
                    ['id' => $m_9->id],
                    ['id' => $m_7->id],
                    ['id' => $m_6->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 8,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.orders.parts'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts'));

        self::assertUnauthenticatedMessage($res);
    }
}
