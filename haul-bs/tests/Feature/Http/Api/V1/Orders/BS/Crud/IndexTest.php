<?php

namespace Feature\Http\Api\V1\Orders\BS\Crud;

use App\Enums\Orders\BS\OrderPaymentStatus;
use App\Enums\Orders\BS\OrderStatus;
use App\Models\Orders\BS\Order;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkInventoryBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected CustomerBuilder $customerBuilder;
    protected OrderBuilder $orderBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected OrderTypeOfWorkBuilder $orderTypeOfWorkBuilder;
    protected OrderTypeOfWorkInventoryBuilder $orderTypeOfWorkInventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->orderTypeOfWorkBuilder = resolve(OrderTypeOfWorkBuilder::class);
        $this->orderTypeOfWorkInventoryBuilder = resolve(OrderTypeOfWorkInventoryBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->create();
        $m_2 = $this->orderBuilder->create();
        $m_3 = $this->orderBuilder->create();
        $this->orderBuilder->deleted()->create();

        $this->getJson(route('api.v1.orders.bs'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'order_number',
                        'vehicle' => [
                            'id',
                            'vin',
                            'make',
                            'model',
                            'year',
                            'unit_number',
                            'vehicle_form',
                        ],
                        'customer',
                        'implementation_date',
                        'mechanic',
                        'total_amount',
                        'status',
                        'notes',
                        'comments_count',
                        'payment_status',
                        'is_overdue',
                        'overdue_days',
                        'billed_at',
                        'paid_at',
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

        $this->getJson(route('api.v1.orders.bs', ['page' => 2]))
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

        $this->getJson(route('api.v1.orders.bs', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.orders.bs'))
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
    public function success_filter_by_vehicle_year()
    {
        $this->loginUserAsSuperAdmin();

        $truck_1 = $this->truckBuilder->year('2003')->create();
        /** @var $m_1 Order */
        $m_1 = $this->orderBuilder->vehicle($truck_1)->status(OrderStatus::New->value)->create();

        $truck_2 = $this->truckBuilder->year('2004')->create();
        $m_2 = $this->orderBuilder->vehicle($truck_2)->create();

        $trailer_1 = $this->trailerBuilder->year('2003')->create();
        $m_3 = $this->orderBuilder->vehicle($trailer_1)->status(OrderStatus::Finished->value)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'vehicle_year' => '2003'
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'status' => $m_1->status->value,
                        'vehicle' => [
                            'id' => $truck_1->id,
                            'vin' => $truck_1->vin,
                            'make' => $truck_1->make,
                            'model' => $truck_1->model,
                            'year' => $truck_1->year,
                            'unit_number' => $truck_1->unit_number,
                            'vehicle_form' => Truck::MORPH_NAME,
                        ]
                    ],
                    [
                        'id' => $m_3->id,
                        'status' => $m_3->status->value,
                        'vehicle' => [
                            'id' => $trailer_1->id,
                            'vehicle_form' => Trailer::MORPH_NAME,
                        ]
                    ],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_vehicle_make()
    {
        $this->loginUserAsSuperAdmin();

        $truck_1 = $this->truckBuilder->make('FORD')->create();
        $m_1 = $this->orderBuilder->vehicle($truck_1)->create();

        $truck_2 = $this->truckBuilder->make('BMW')->create();
        $m_2 = $this->orderBuilder->vehicle($truck_2)->create();

        $trailer_1 = $this->trailerBuilder->make('MAN')->create();
        $m_3 = $this->orderBuilder->vehicle($trailer_1)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'vehicle_make' => 'MAN'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_vehicle_model()
    {
        $this->loginUserAsSuperAdmin();

        $truck_1 = $this->truckBuilder->model('FORD')->create();
        $m_1 = $this->orderBuilder->vehicle($truck_1)->create();

        $truck_2 = $this->truckBuilder->model('BMW')->create();
        $m_2 = $this->orderBuilder->vehicle($truck_2)->create();

        $trailer_1 = $this->trailerBuilder->model('MAN')->create();
        $m_3 = $this->orderBuilder->vehicle($trailer_1)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'vehicle_model' => 'MAN'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
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

        $m_1 = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $m_2 = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $m_3 = $this->orderBuilder->status(OrderStatus::Finished->value)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'status' => OrderStatus::New->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_2->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_status_as_deleted()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $m_2 = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $m_3 = $this->orderBuilder->status(OrderStatus::Deleted->value)->deleted()->create();

        $this->getJson(route('api.v1.orders.bs', [
            'status' => OrderStatus::Deleted->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_paid()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->is_paid(true)->create();
        $m_2 = $this->orderBuilder->is_paid(false)->create();
        $m_3 = $this->orderBuilder->is_paid(false)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'payment_status' => OrderPaymentStatus::Paid->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_not_paid()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->is_paid(true)->create();
        $m_2 = $this->orderBuilder->is_paid(false)->create();
        $m_3 = $this->orderBuilder->is_paid(false)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'payment_status' => OrderPaymentStatus::Not_paid->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id,],
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_billed()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->is_paid(true)->is_billed(true)->create();
        $m_2 = $this->orderBuilder->is_paid(false)->is_billed(true)->create();
        $m_3 = $this->orderBuilder->is_paid(false)->is_billed(false)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'payment_status' => OrderPaymentStatus::Billed->value
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
    public function success_filter_by_payment_status_not_billed()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->is_paid(true)->is_billed(true)->create();
        $m_2 = $this->orderBuilder->is_paid(false)->is_billed(true)->create();
        $m_3 = $this->orderBuilder->is_paid(false)->is_billed(false)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'payment_status' => OrderPaymentStatus::Not_billed->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_overdue()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $m_1 = $this->orderBuilder->is_paid(true)->status(OrderStatus::In_process())
            ->due_date($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->is_paid(false)->status(OrderStatus::In_process())
            ->due_date($now->subDays(2))->create();
        $m_3 = $this->orderBuilder->is_paid(true)->status(OrderStatus::In_process())
            ->due_date($now->addDays(2))->create();
        $m_4 = $this->orderBuilder->is_paid(false)->status(OrderStatus::In_process())
            ->due_date($now->subDays(2))->create();

        $this->getJson(route('api.v1.orders.bs', [
            'payment_status' => OrderPaymentStatus::Overdue->value
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id,],
                    ['id' => $m_4->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_payment_status_not_overdue()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $m_1 = $this->orderBuilder->is_paid(true)->due_date($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->is_paid(false)->due_date($now->subDays(2))->create();
        $m_3 = $this->orderBuilder->is_paid(true)->due_date($now->addDays(2))->create();
        $m_4 = $this->orderBuilder->is_paid(false)->due_date($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.bs', [
            'payment_status' => OrderPaymentStatus::Not_overdue->value
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
    public function success_filter_by_date_from()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $m_1 = $this->orderBuilder->implementation_date($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->implementation_date($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.bs', [
            'date_from' => $now->format('Y-m-d H:i')
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id,],
                    ['id' => $m_3->id,],
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

        $m_1 = $this->orderBuilder->implementation_date($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->implementation_date($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.bs', [
            'date_to' => $now->format('Y-m-d H:i')
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_2->id,],
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

        $m_1 = $this->orderBuilder->implementation_date($now->subDays(4))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->implementation_date($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.bs', [
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
    public function success_filter_by_inventory()
    {
        $this->loginUserAsSuperAdmin();

        $inventory_1 = $this->inventoryBuilder->create();
        $inventory_2 = $this->inventoryBuilder->create();
        $inventory_3 = $this->inventoryBuilder->create();

        $m_1 = $this->orderBuilder->create();
        $w_1 = $this->orderTypeOfWorkBuilder->order($m_1)->create();
        $this->orderTypeOfWorkInventoryBuilder->type_of_work($w_1)->inventory($inventory_1)->create();

        $m_2 = $this->orderBuilder->create();
        $w_2 = $this->orderTypeOfWorkBuilder->order($m_2)->create();
        $this->orderTypeOfWorkInventoryBuilder->type_of_work($w_2)->inventory($inventory_2)->create();

        $m_3 = $this->orderBuilder->create();
        $w_3 = $this->orderTypeOfWorkBuilder->order($m_3)->create();
        $this->orderTypeOfWorkInventoryBuilder->type_of_work($w_3)->inventory($inventory_1)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'inventory_id' => $inventory_1->id,
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_truck_id()
    {
        $this->loginUserAsSuperAdmin();

        $truck_1 = $this->truckBuilder->create();
        $truck_2 = $this->truckBuilder->create();
        $trailer_1 = $this->trailerBuilder->create();

        $m_1 = $this->orderBuilder->vehicle($truck_1)->create();
        $m_2 = $this->orderBuilder->vehicle($truck_2)->create();
        $m_3 = $this->orderBuilder->vehicle($trailer_1)->create();


        $this->getJson(route('api.v1.orders.bs', [
            'truck_id' => $truck_1->id,
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_trailer_id()
    {
        $this->loginUserAsSuperAdmin();

        $truck_1 = $this->truckBuilder->create();
        $truck_2 = $this->truckBuilder->create();
        $trailer_1 = $this->trailerBuilder->create();

        $m_1 = $this->orderBuilder->vehicle($truck_1)->create();
        $m_2 = $this->orderBuilder->vehicle($truck_2)->create();
        $m_3 = $this->orderBuilder->vehicle($trailer_1)->create();
        $m_4 = $this->orderBuilder->vehicle($trailer_1)->create();

        $this->getJson(route('api.v1.orders.bs', [
            'trailer_id' => $trailer_1->id,
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
    public function success_filter_by_mechanic_id()
    {
        $this->loginUserAsSuperAdmin();

        $user_1 = $this->userBuilder->asMechanic()->create();
        $user_2 = $this->userBuilder->asMechanic()->create();

        $m_1 = $this->orderBuilder->mechanic($user_1)->create();
        $m_2 = $this->orderBuilder->mechanic($user_2)->create();
        $m_3 = $this->orderBuilder->mechanic($user_1)->create();


        $this->getJson(route('api.v1.orders.bs', [
            'mechanic_id' => $user_1->id,
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_order_number()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->order_number('34Tuscon8999')->create();
        $m_2 = $this->orderBuilder->order_number('34Veer8999')->create();
        $m_3 = $this->orderBuilder->order_number('34Wood8999')->create();

        $this->getJson(route('api.v1.orders.bs', [
            'search' => '34Tuscon89',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_vin_and_unit_number()
    {
        $this->loginUserAsSuperAdmin();

        $truck_1 = $this->truckBuilder->vin('44555444556784')->unit_number('wert')->create();
        $truck_2 = $this->truckBuilder->vin('99555222556783')->unit_number('wert_45')->create();
        $trailer_1 = $this->trailerBuilder->vin('9090909090')->unit_number('we445554')->create();

        $m_1 = $this->orderBuilder->order_number('34Tuscon8999')->vehicle($truck_1)->create();
        $m_2 = $this->orderBuilder->order_number('34Veer8999')->vehicle($truck_2)->create();
        $m_3 = $this->orderBuilder->order_number('34Wood8999')->vehicle($trailer_1)->create();


        $this->getJson(route('api.v1.orders.bs', [
            'search' => '445554',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_search_by_customer_name()
    {
        $this->loginUserAsSuperAdmin();

        $customer_1 = $this->customerBuilder->first_name('aaaaa')->last_name('werty')->create();
        $customer_2 = $this->customerBuilder->first_name('bbbbb')->last_name('werty')->create();
        $customer_3 = $this->customerBuilder->first_name('zzzzz')->last_name('aaaaaa')->create();

        $truck_1 = $this->truckBuilder->vin('44555444556784')->customer($customer_1)
            ->unit_number('wert')->create();
        $truck_2 = $this->truckBuilder->vin('99555222556783')->customer($customer_2)
            ->unit_number('wert_45')->create();
        $trailer_1 = $this->trailerBuilder->vin('9090909090')->customer($customer_3)
            ->unit_number('we445554')->create();

        $m_1 = $this->orderBuilder->order_number('34Tuscon8999')->vehicle($truck_1)->create();
        $m_2 = $this->orderBuilder->order_number('34Veer8999')->vehicle($truck_2)->create();
        $m_3 = $this->orderBuilder->order_number('34Wood8999')->vehicle($trailer_1)->create();


        $this->getJson(route('api.v1.orders.bs', [
            'search' => 'aaaaa',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id,],
                    ['id' => $m_3->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.orders.bs'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.bs'));

        self::assertUnauthenticatedMessage($res);
    }
}
