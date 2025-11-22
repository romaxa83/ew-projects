<?php

namespace Feature\Http\Api\V1\Orders\BS\Report;

use App\Enums\Orders\BS\OrderPaymentStatus;
use App\Enums\Orders\BS\OrderStatus;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
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

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->create();
        $m_2 = $this->orderBuilder->create();
        $m_3 = $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.bs.report'))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'order_number',
                        'total_amount',
                        'implementation_date',
                        'customer',
                        'customer_id',
                        'status',
                        'is_paid',
                        'current_due',
                        'past_due',
                        'total_due',
                        'parts_cost',
                        'profit',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
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

        $this->getJson(route('api.v1.orders.bs.report', ['page' => 2]))
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

        $this->getJson(route('api.v1.orders.bs.report', ['per_page' => 2]))
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

        $this->getJson(route('api.v1.orders.bs.report'))
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
    public function success_pagination_sort_default_desc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        $m_1 = $this->orderBuilder->implementation_date($now->subMinutes(10))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subMinutes(9))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->subMinutes(8))->create();

        $this->getJson(route('api.v1.orders.bs.report'))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_sort_default_asc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        $m_1 = $this->orderBuilder->implementation_date($now->subMinutes(10))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subMinutes(9))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->subMinutes(8))->create();

        $this->getJson(route('api.v1.orders.bs.report',[
            'order_type' => 'asc'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_sort_current_due_asc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        // with current due
        $m_1 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // with past due
        $m_2 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // paid
        $m_3 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(100)
            ->debt_amount(0)
            ->create();
        // with current due
        $m_4 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(70)
            ->debt_amount(30)
            ->create();
        // with past due
        $m_5 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(40)
            ->debt_amount(60)
            ->create();

        $params = ['order_by' => 'current_due', 'order_type' => 'asc'];
        $this->getJson(route('api.v1.orders.bs.report', $params))
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id],
                    ['id' => $m_1->id],
                    ['id' => $m_5->id],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 5,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_sort_current_due_desc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        // with current due
        $m_1 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // with past due
        $m_2 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // paid
        $m_3 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(100)
            ->debt_amount(0)
            ->create();
        // with current due
        $m_4 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(70)
            ->debt_amount(30)
            ->create();
        // with past due
        $m_5 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(40)
            ->debt_amount(60)
            ->create();

        $params = ['order_by' => 'current_due', 'order_type' => 'desc'];
        $this->getJson(route('api.v1.orders.bs.report', $params))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_4->id],
                    ['id' => $m_5->id],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 5,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_sort_past_due_asc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        // with current due
        $m_1 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // with past due
        $m_2 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // paid
        $m_3 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(100)
            ->debt_amount(0)
            ->create();
        // with current due
        $m_4 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(70)
            ->debt_amount(30)
            ->create();
        // with past due
        $m_5 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(40)
            ->debt_amount(60)
            ->create();

        $params = ['order_by' => 'past_due', 'order_type' => 'asc'];
        $this->getJson(route('api.v1.orders.bs.report', $params))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_5->id],
                    ['id' => $m_4->id],
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 5,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_sort_past_due_desc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        // with current due
        $m_1 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // with past due
        $m_2 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // paid
        $m_3 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(100)
            ->debt_amount(0)
            ->create();
        // with current due
        $m_4 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(70)
            ->debt_amount(30)
            ->create();
        // with past due
        $m_5 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(40)
            ->debt_amount(60)
            ->create();

        $params = ['order_by' => 'past_due', 'order_type' => 'desc'];
        $this->getJson(route('api.v1.orders.bs.report', $params))
            ->assertJson([
                'data' => [
                    ['id' => $m_5->id],
                    ['id' => $m_2->id],
                    ['id' => $m_4->id],
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 5,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_sort_total_due_asc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        // with current due
        $m_1 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // with past due
        $m_2 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // paid
        $m_3 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(100)
            ->debt_amount(0)
            ->create();
        // with current due
        $m_4 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(70)
            ->debt_amount(30)
            ->create();
        // with past due
        $m_5 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(40)
            ->debt_amount(60)
            ->create();

        $params = ['order_by' => 'total_due', 'order_type' => 'asc'];
        $this->getJson(route('api.v1.orders.bs.report', $params))
            ->assertJson([
                'data' => [
                    ['id' => $m_4->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                    ['id' => $m_5->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 5,
                ]
            ])
        ;
    }

    /** @test */
    public function success_pagination_sort_total_due_desc()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        // with current due
        $m_1 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // with past due
        $m_2 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->create();
        // paid
        $m_3 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(100)
            ->debt_amount(0)
            ->create();
        // with current due
        $m_4 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(70)
            ->debt_amount(30)
            ->create();
        // with past due
        $m_5 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(40)
            ->debt_amount(60)
            ->create();

        $params = ['order_by' => 'total_due', 'order_type' => 'desc'];
        $this->getJson(route('api.v1.orders.bs.report', $params))
            ->assertJson([
                'data' => [
                    ['id' => $m_5->id],
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                    ['id' => $m_4->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 5,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_statuses()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $m_2 = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $m_3 = $this->orderBuilder->status(OrderStatus::Finished->value)->create();
        $m_4 = $this->orderBuilder->status(OrderStatus::In_process->value)->create();

        $this->getJson(route('api.v1.orders.bs.report', [
            'statuses' => [OrderStatus::Finished->value, OrderStatus::In_process->value]
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
    public function success_filter_by_payment_statuses()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->is_paid(true)->create();
        $m_2 = $this->orderBuilder->is_paid(false)->create();
        $m_3 = $this->orderBuilder->is_paid(false)->create();
        $m_4 = $this->orderBuilder->is_paid(false)->is_billed(true)->create();

        $this->getJson(route('api.v1.orders.bs.report', [
            'payment_statuses' => [
                OrderPaymentStatus::Paid->value,
            ]
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
    public function success_filter_by_date_from()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $m_1 = $this->orderBuilder->implementation_date($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->implementation_date($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.bs.report', [
            'implementation_date_from' => $now->format('Y-m-d H:i')
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

        $m_1 = $this->orderBuilder->implementation_date($now->subDays(2))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->implementation_date($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.bs.report', [
            'implementation_date_to' => $now->format('Y-m-d H:i')
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

        $m_1 = $this->orderBuilder->implementation_date($now->subDays(4))->create();
        $m_2 = $this->orderBuilder->implementation_date($now->subDays(1))->create();
        $m_3 = $this->orderBuilder->implementation_date($now->addDays(5))->create();
        $m_4 = $this->orderBuilder->implementation_date($now->addDays(2))->create();

        $this->getJson(route('api.v1.orders.bs.report', [
            'implementation_date_from' => $now->subDays(3)->format('Y-m-d H:i'),
            'implementation_date_to' => $now->format('Y-m-d H:i')
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
    public function success_search_by_order_number()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->orderBuilder->order_number('34Tuscon8999')->create();
        $m_2 = $this->orderBuilder->order_number('34Veer8999')->create();
        $m_3 = $this->orderBuilder->order_number('34Wood8999')->create();

        $this->getJson(route('api.v1.orders.bs.report', [
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


        $this->getJson(route('api.v1.orders.bs.report', [
            'search' => '445554',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                    ['id' => $m_1->id,],
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


        $this->getJson(route('api.v1.orders.bs.report', [
            'search' => 'aaaaa',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id,],
                    ['id' => $m_1->id,],
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

        $res = $this->getJson(route('api.v1.orders.bs.report'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.bs.report'));

        self::assertUnauthenticatedMessage($res);
    }
}
