<?php

namespace Tests\Feature\Reports\Efficiency;

use App\Models\Division;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Orders\EstimateCalculatedBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\PaymentBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Orders\StatusHistoryBuilder;
use Tests\Builders\Users\RoleBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class DatatableTest extends TestCase
{
    use DatabaseTransactions;
    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected RoleBuilder $roleBuilder;
    protected OrderBuilder $orderBuilder;
    protected StatusBuilder $orderStatusBuilder;
    protected StatusHistoryBuilder $orderStatusHistoryBuilder;
    protected EstimateCalculatedBuilder $estimateCalculatedBuilder;
    protected PaymentBuilder $paymentBuilder;


    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->orderStatusBuilder = resolve(StatusBuilder::class);
        $this->orderStatusHistoryBuilder = resolve(StatusHistoryBuilder::class);
        $this->estimateCalculatedBuilder = resolve(EstimateCalculatedBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function group_by_manager_check_order_by_status_as_calculation_done()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $managerRole = $this->roleBuilder->id(5)
            ->asManager()->create();

        $user_1 = $this->userBuilder
            ->division_ids($division)
            ->roles($managerRole)->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();

        $statusNewLead  = $this->orderStatusBuilder
            ->id(1)
            ->create();
        $statusDone  = $this->orderStatusBuilder
            ->id(4)
            ->create();

        /** @var $order_1 Order */
        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusDone)
            ->created_at($now->subDays())
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($statusDone)
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_1)
            ->title('total')
            ->value("$100")
            ->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusDone)
            ->created_at($now->subDays(3))
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($statusDone)
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_2)
            ->title('total')
            ->value("$100.50")
            ->create();

        // not, last date
        $order_3 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusDone)
            ->created_at($now->subDays(4))
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($statusDone)
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_3)
            ->title('total')
            ->value("$1,050")
            ->create();

        $filter = [
            'filter' => [
                'start-range' => $now->subDays(5)->format('Y-m-d'),
                'end-range' => $now->format('Y-m-d'),
                'period-type' => 'by_creation',
                'groupBy' => 'manager',
            ]
        ];

        $this->post(route('reports.efficiency.datatable'), $filter)
//            ->dump()
            ->assertJson([
                'draw' => 0,
                'recordsTotal' => 13,
                'recordsFiltered' => 13,
                'data' => [
                    [
                        'id' => 1,
                        'user_'.$user_1->id => 3,
                        'user_0' => "",
                    ],
                    [
                        'id' => 2,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 3,
                        'user_'.$user_1->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 4,
                        'user_'.$user_1->id => 3,
                        'user_0' => "",
                    ],
                    [
                        'id' => 5,
                        'user_'.$user_1->id => "100%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 6,
                        'user_'.$user_1->id => "$1250.5",
                        'user_0' => "",
                    ],
                    [
                        'id' => 7,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 8,
                        'user_'.$user_1->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 9,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 10,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 11,
                        'user_'.$user_1->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 12,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 13,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                ],
            ])
            ->assertJsonCount(13, 'data')
            ->assertJsonCount(5, 'data.0')
            ->assertJsonCount(5, 'data.1')
            ->assertJsonCount(5, 'data.2')
        ;
    }

    /** @test */
    public function group_by_manager_check_order_by_status_as_new_lead_and_success()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $adminRole = $this->roleBuilder
            ->asAdmin()->create();
        $managerRole = $this->roleBuilder
            ->asManager()->create();

        $user_1 = $this->userBuilder
            ->division_ids($division)
            ->roles($managerRole)->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();

        $user_2 = $this->userBuilder
            ->division_ids($division)
            ->roles($adminRole)->create();
        $employee_2 = $this->employeeBuilder->user($user_2)->create();

        // not, different division
        $user_3 = $this->userBuilder->create();
        $employee_3 = $this->employeeBuilder->user($user_3)->create();

        $statusNewLead  = $this->orderStatusBuilder
            ->id(1)
            ->create();
        $statusSuccess  = $this->orderStatusBuilder
            ->id(10)
            ->create();

        /** @var $order_1 Order */
        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusSuccess)
            ->created_at($now->subDays())
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($statusSuccess)
            ->create();
        $payment_1_1 = $this->paymentBuilder
            ->order($order_1)->amount(50.0000)->create();
        $payment_1_2 = $this->paymentBuilder
            ->order($order_1)->amount(150.0000)->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusSuccess)
            ->created_at($now->subDays(3))
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($statusSuccess)
            ->create();
        $payment_1_2 = $this->paymentBuilder
            ->order($order_2)->amount(300.5000)->create();

        // not, last date
        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusNewLead)
            ->created_at($now->subDays(7))
            ->create();

        $filter = [
            'filter' => [
                'start-range' => $now->subDays(5)->format('Y-m-d'),
                'end-range' => $now->format('Y-m-d'),
                'period-type' => 'by_creation',
                'groupBy' => 'manager',
            ]
        ];

        $this->post(route('reports.efficiency.datatable'), $filter)
            ->assertJson([
                'draw' => 0,
                'recordsTotal' => 13,
                'recordsFiltered' => 13,
                'data' => [
                    [
                        'id' => 1,
                        'user_'.$user_1->id => 2,
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 2,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 3,
                        'user_'.$user_1->id => "0%",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 4,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 5,
                        'user_'.$user_1->id => "0%",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 6,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 7,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 8,
                        'user_'.$user_1->id => "0%",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 9,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 10,
                        'user_'.$user_1->id => 2,
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 11,
                        'user_'.$user_1->id => "100%",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 12,
                        'user_'.$user_1->id => "$500.5",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 13,
                        'user_'.$user_1->id => "$250.25",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                ],
            ])
            ->assertJsonCount(13, 'data')
            ->assertJsonCount(6, 'data.0')
            ->assertJsonCount(6, 'data.1')
            ->assertJsonCount(6, 'data.2')
        ;
    }

    /** @test */
    public function group_by_manager_check_order_by_status_as_lost()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $adminRole = $this->roleBuilder
            ->asAdmin()->create();
        $managerRole = $this->roleBuilder
            ->asManager()->create();

        $user_1 = $this->userBuilder
            ->division_ids($division)
            ->roles($managerRole)->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();

        $user_2 = $this->userBuilder
            ->division_ids($division)
            ->roles($adminRole)->create();
        $employee_2 = $this->employeeBuilder->user($user_2)->create();

        // not, different division
        $user_3 = $this->userBuilder->create();
        $employee_3 = $this->employeeBuilder->user($user_3)->create();

        $statusNewLead  = $this->orderStatusBuilder
            ->id(1)
            ->create();
        $statusLost  = $this->orderStatusBuilder
            ->id(9)
            ->create();

        /** @var $order_1 Order */
        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_2)
            ->status($statusNewLead)
            ->created_at($now->subDays())
            ->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusLost)
            ->created_at($now->subDays(3))
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($statusLost)
            ->create();

        $order_3 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusLost)
            ->created_at($now->subDays(2))
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($statusLost)
            ->create();

        $filter = [
            'filter' => [
                'start-range' => $now->subDays(5)->format('Y-m-d'),
                'end-range' => $now->format('Y-m-d'),
                'period-type' => 'by_creation',
                'groupBy' => 'manager',
            ]
        ];

        $this->post(route('reports.efficiency.datatable'), $filter)
            ->assertJson([
                'draw' => 0,
                'recordsTotal' => 13,
                'recordsFiltered' => 13,
                'data' => [
                    [
                        'id' => 1,
                        'user_'.$user_1->id => 2,
                        'user_'.$user_2->id => 1,
                        'user_0' => "",
                    ],
                    [
                        'id' => 2,
                        'user_'.$user_1->id => 2,
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 3,
                        'user_'.$user_1->id => "100%",
                        'user_'.$user_2->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 4,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 5,
                        'user_'.$user_1->id => "0%",
                        'user_'.$user_2->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 6,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 7,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 8,
                        'user_'.$user_1->id => "0%",
                        'user_'.$user_2->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 9,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 10,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 11,
                        'user_'.$user_1->id => "0%",
                        'user_'.$user_2->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 12,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 13,
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                ],
            ])
            ->assertJsonCount(13, 'data')
            ->assertJsonCount(6, 'data.0')
            ->assertJsonCount(6, 'data.1')
            ->assertJsonCount(6, 'data.2')
        ;
    }

    /** @test */
    public function group_by_manager_check_order_by_status_as_booked_with_calc()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        $managerRole = $this->roleBuilder->id(5)
            ->asManager()->create();

        $user_1 = $this->userBuilder
            ->division_ids($division)
            ->roles($managerRole)->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();

        $statusNewLead  = $this->orderStatusBuilder
            ->id(1)
            ->create();
        $statusBooked  = $this->orderStatusBuilder
            ->id(5)
            ->create();

        /** @var $order_1 Order */
        $order_1 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusBooked)
            ->created_at($now->subDays())
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_1)
            ->prev_status($statusNewLead)
            ->new_status($statusBooked)
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_1)
            ->title('total')
            ->value("$100")
            ->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusBooked)
            ->created_at($now->subDays(3))
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_2)
            ->prev_status($statusNewLead)
            ->new_status($statusBooked)
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_2)
            ->title('total')
            ->value("$200")
            ->create();

        $order_3 = $this->orderBuilder
            ->division($division)
            ->manager($user_1)
            ->status($statusBooked)
            ->created_at($now->subDays(2))
            ->create();
        $this->orderStatusHistoryBuilder
            ->order_id($order_3)
            ->prev_status($statusNewLead)
            ->new_status($statusBooked)
            ->create();
        $this->estimateCalculatedBuilder
            ->order($order_3)
            ->title('total')
            ->value("$8,050.95")
            ->create();

        $filter = [
            'filter' => [
                'start-range' => $now->subDays(5)->format('Y-m-d'),
                'end-range' => $now->format('Y-m-d'),
                'period-type' => 'by_creation',
                'groupBy' => 'manager',
            ]
        ];

        $this->post(route('reports.efficiency.datatable'), $filter)
            ->assertJson([
                'draw' => 0,
                'recordsTotal' => 13,
                'recordsFiltered' => 13,
                'data' => [
                    [
                        'id' => 1,
                        'user_'.$user_1->id => 3,
                        'user_0' => "",
                    ],
                    [
                        'id' => 2,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 3,
                        'user_'.$user_1->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 4,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 5,
                        'user_'.$user_1->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 6,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 7,
                        'user_'.$user_1->id => 3,
                        'user_0' => "",
                    ],
                    [
                        'id' => 8,
                        'user_'.$user_1->id => "100%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 9,
                        'user_'.$user_1->id => "$8350.95",
                        'user_0' => "",
                    ],
                    [
                        'id' => 10,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 11,
                        'user_'.$user_1->id => "0%",
                        'user_0' => "",
                    ],
                    [
                        'id' => 12,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 13,
                        'user_'.$user_1->id => "",
                        'user_0' => "",
                    ],
                ],
            ])
            ->assertJsonCount(13, 'data')
            ->assertJsonCount(5, 'data.0')
            ->assertJsonCount(5, 'data.1')
            ->assertJsonCount(5, 'data.2')
        ;
    }

    /** @test */
    public function group_by_manager_empty_order()
    {
        $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder
            ->division($division)->create();

        $adminRole = $this->roleBuilder
            ->asAdmin()->create();
        $managerRole = $this->roleBuilder
            ->asManager()->create();

        $user_1 = $this->userBuilder
            ->division_ids($division)
            ->roles($managerRole)->create();
        $employee_1 = $this->employeeBuilder->user($user_1)->create();

        $user_2 = $this->userBuilder
            ->division_ids($division)
            ->roles($adminRole)->create();
        $employee_2 = $this->employeeBuilder->user($user_2)->create();

        $user_3 = $this->userBuilder->create();
        $employee_3 = $this->employeeBuilder->user($user_3)->create();

        $filter = [
            'filter' => [
                'start-range' => $now->subDays(5)->format('Y-m-d'),
                'end-range' => $now->format('Y-m-d'),
                'period-type' => 'by_creation',
                'groupBy' => 'manager',
            ]
        ];

        $this->post(route('reports.efficiency.datatable'), $filter)
            ->assertJson([
                'draw' => 0,
                'recordsTotal' => 13,
                'recordsFiltered' => 13,
                'data' => [
                    [
                        'id' => 1,
                        'type' => 'LeadsTotal',
                        'title' => 'All incoming leads, qty',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 2,
                        'type' => 'LeadsLost',
                        'title' => 'Lost leads, qty',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 3,
                        'type' => 'LeadsLostCR',
                        'title' => 'Lost CR, %',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 4,
                        'type' => 'LeadsCalculated',
                        'title' => 'Calculation Done passed leads, qty',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 5,
                        'type' => 'LeadsCalculatedCR',
                        'title' => 'Calculation Done passed leads CR, %',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 6,
                        'type' => 'LeadsCalculatedSum',
                        'title' => 'Calculation Done, est. sum $',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 7,
                        'type' => 'LeadsBooked',
                        'title' => 'Booked passed leads, qty',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 8,
                        'type' => 'LeadsBookedCR',
                        'title' => 'Booked passed leads CR, %',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 9,
                        'type' => 'LeadsBookedSum',
                        'title' => 'Booked leads, est. sum $',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 10,
                        'type' => 'LeadsSuccessful',
                        'title' => 'Successful leads, qty',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 11,
                        'type' => 'LeadsSuccessfulCR',
                        'title' => 'Successful CR, %',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 12,
                        'type' => 'SuccessRevenue',
                        'title' => 'Successful revenue, $',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                    [
                        'id' => 13,
                        'type' => 'SuccessAOV',
                        'title' => 'Successful AOV, $',
                        'user_'.$user_1->id => "",
                        'user_'.$user_2->id => "",
                        'user_0' => "",
                    ],
                ],
                'cols' => [
                    'col1',
                    'col2'
                ],
                'input' => $filter
            ])
            ->assertJsonCount(13, 'data')
            ->assertJsonCount(6, 'data.0')
            ->assertJsonCount(6, 'data.1')
            ->assertJsonCount(6, 'data.2')
        ;
    }
}



