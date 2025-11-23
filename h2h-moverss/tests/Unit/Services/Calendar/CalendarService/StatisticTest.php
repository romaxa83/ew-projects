<?php

namespace Tests\Unit\Services\Calendar\CalendarService;

use App\Models\Division;
use App\Services\Calendars\CalendarService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\DispatchEmployeeBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\StatusBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Trucks\DispatchTruckBuilder;
use Tests\TestCase;

class StatisticTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected OrderBuilder $orderBuilder;
    protected WorkBuilder $workBuilder;
    protected DispatchEmployeeBuilder $dispatchEmployeeBuilder;
    protected DispatchTruckBuilder $dispatchTruckBuilder;
    protected StatusBuilder $statusBuilder;
    protected CalendarService $service;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->dispatchEmployeeBuilder = resolve(DispatchEmployeeBuilder::class);
        $this->dispatchTruckBuilder = resolve(DispatchTruckBuilder::class);
        $this->statusBuilder = resolve(StatusBuilder::class);
        $this->service = resolve(CalendarService::class);


        parent::setUp();
    }

    /** @test */
    public function check_not_close_order()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $now = CarbonImmutable::now();
        $startPeriod = $now->startOfMonth();
        $endPeriod = $startPeriod->addDays(7);

        $status_1 = $this->statusBuilder
            ->actions(['disable_dispatch'])
            ->asBooked()
            ->create();
        $status_2 = $this->statusBuilder
            ->actions(['enable_dispatch'])
            ->asDuplicate()
            ->create();
        $status_3 = $this->statusBuilder
            ->actions(['enable_dispatch'])
            ->asNewLead()
            ->create();
        $status_4 = $this->statusBuilder
            ->actions(['enable_dispatch'])
            ->asSalesDone()
            ->create();

        $order_1_1 = $this->orderBuilder
            ->division($division)
            ->status($status_2)
            ->create();
        $work_1 = $this->workBuilder
            ->start_date($now->startOfMonth()->addDays(1)->format('Y-m-d'))
            ->order($order_1_1)
            ->create();
        $this->dispatchTruckBuilder->work($work_1)->create();

        $order_1_2 = $this->orderBuilder
            ->division($division)
            ->status($status_1)
            ->create();
        $work_1_2 = $this->workBuilder
            ->start_date($now->startOfMonth()->addDays(1)->format('Y-m-d'))
            ->order($order_1_2)
            ->create();
        $this->dispatchEmployeeBuilder->work($work_1_2)->create();

        // not has dispatchTruck or dispatchEmployee
        $order_1_3 = $this->orderBuilder
            ->division($division)
            ->status($status_2)
            ->create();
        $work_1_3 = $this->workBuilder
            ->start_date($now->startOfMonth()->addDays(1)->format('Y-m-d'))
            ->order($order_1_3)
            ->create();

        // not has work
        $this->orderBuilder
            ->division($division)
            ->status($status_3)
            ->create();

        // status salesDone
        $order_1_4 =  $this->orderBuilder
            ->division($division)
            ->status($status_4)
            ->create();
        $work_1_4 = $this->workBuilder
            ->start_date($now->startOfMonth()->addDays(1)->format('Y-m-d'))
            ->order($order_1_4)
            ->create();
        $this->dispatchEmployeeBuilder->work($work_1_4)->create();

        $order_2 = $this->orderBuilder
            ->division($division)
            ->status($status_2)
            ->create();
        $work_2 = $this->workBuilder
            ->start_date($now->startOfMonth()->addDays(2)->format('Y-m-d'))
            ->order($order_2)
            ->create();

        $order_3 = $this->orderBuilder
            ->division($division)
            ->status($status_2)
            ->create();
        $work_3 = $this->workBuilder
            ->start_date($now->startOfMonth()->addDays(3)->format('Y-m-d'))
            ->order($order_3)
            ->create();

        $result = $this->service->statistics(
            $startPeriod,
            $endPeriod,
            $division->id,
            "America/Chicago"
        );

        $this->assertEquals(
            2,
            $result[$now->startOfMonth()->addDays(1)->format('Y-m-d')]['not_close_order']
        );
        $this->assertEquals(
            0,
            $result[$now->startOfMonth()->addDays(2)->format('Y-m-d')]['not_close_order']
        );
        $this->assertEquals(
            0,
            $result[$now->startOfMonth()->addDays(3)->format('Y-m-d')]['not_close_order']
        );
    }
}

