<?php

namespace Tests\Unit\Services\Audit\AuditFetchService;

use App\Models\DispatchEmployer;
use App\Models\DispatchTruck;
use App\Models\Division;
use App\Models\Order;
use App\Models\Order\Work;
use App\Models\Truck\Truck;
use App\Services\Audit\AuditFetchService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\DispatchEmployeeBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Trucks\DispatchTruckBuilder;
use Tests\Builders\Trucks\TruckBuilder;
use Tests\TestCase;

class AuditPaginatorByDispatchTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected WorkBuilder $workBuilder;
    protected TruckBuilder $truckBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected DispatchEmployeeBuilder $dispatchEmployeeBuilder;
    protected DispatchTruckBuilder $dispatchTruckBuilder;
    protected OrderBuilder $orderBuilder;
    protected AuditBuilder $auditBuilder;

    protected AuditFetchService $service;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->dispatchEmployeeBuilder = resolve(DispatchEmployeeBuilder::class);
        $this->dispatchTruckBuilder = resolve(DispatchTruckBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(AuditFetchService::class);


        parent::setUp();
    }

    /** @test */
    public function success_only_for_dispatch()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $order->audits()->delete();

        /** @var $work Work */
        $work = $this->workBuilder->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();
        /** @var $dispatchTruck DispatchTruck */
        $dispatchTruck = $this->dispatchTruckBuilder
            ->create();
        $dispatchTruck->audits()->delete();
        /** @var $dispatchEmployer DispatchEmployer */
        $dispatchEmployer = $this->dispatchEmployeeBuilder
            ->create();
        $dispatchEmployer->audits()->delete();

        $this->auditBuilder->order($order)
            ->auditable($order)
            ->new_values(['test' => 1])->create();

        $rec_1 = $this->auditBuilder
            ->auditable($dispatchTruck)
            ->new_values([
                'truck_id' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->created_at($date->subHours(4))
            ->create();
        $rec_2 =  $this->auditBuilder
            ->auditable($dispatchEmployer)
            ->new_values([
                'employee_id' => $dispatchEmployer->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->created_at($date->subHours(3))
            ->create();

        $this->auditBuilder->create();

        $res = $this->service->getAuditPaginatorByDispatch();

        $this->assertTrue($res instanceof LengthAwarePaginator);

        $this->assertEquals(count($res->items()), 2);

        $this->assertEquals($res->items()[0]->id, $rec_2->id);
        $this->assertEquals($res->items()[1]->id, $rec_1->id);
    }

    /** @test */
    public function success_filter_by_date()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $order->audits()->delete();

        /** @var $work Work */
        $work = $this->workBuilder->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();
        /** @var $dispatchTruck DispatchTruck */
        $dispatchTruck = $this->dispatchTruckBuilder
            ->create();
        $dispatchTruck->audits()->delete();
        /** @var $dispatchEmployer DispatchEmployer */
        $dispatchEmployer = $this->dispatchEmployeeBuilder
            ->create();
        $dispatchEmployer->audits()->delete();

        $rec_1 = $this->auditBuilder
            ->auditable($dispatchTruck)
            ->new_values([
                'truck_id' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->created_at($date->subMinutes(4))
            ->dispatch_truck_at($date->format('Y-m-d'))
            ->create();
        $rec_2 =  $this->auditBuilder
            ->auditable($dispatchEmployer)
            ->new_values([
                'employee_id' => $dispatchEmployer->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->dispatch_truck_at($date->subDays(2)->format('Y-m-d'))
            ->created_at($date->subDays(2))
            ->create();

        $res = $this->service->getAuditPaginatorByDispatch([
            'start_date' => $date->subDays(2)->format('Y-m-d'),
        ]);

        $this->assertTrue($res instanceof LengthAwarePaginator);

        $this->assertEquals(count($res->items()), 1);

        $this->assertEquals($res->items()[0]->id, $rec_2->id);
    }

    /** @test */
    public function success_filter_by_division()
    {
        $date = CarbonImmutable::now();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $division_1 = $this->divisionBuilder->create();

        /** @var $work Work */
        $work = $this->workBuilder->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();
        /** @var $dispatchTruck DispatchTruck */
        $dispatchTruck = $this->dispatchTruckBuilder
            ->create();
        $dispatchTruck->audits()->delete();
        /** @var $dispatchEmployer DispatchEmployer */
        $dispatchEmployer = $this->dispatchEmployeeBuilder
            ->create();
        $dispatchEmployer->audits()->delete();
        $dispatchEmployer_1 = $this->dispatchEmployeeBuilder
            ->create();
        $dispatchEmployer_1->audits()->delete();

        $rec_1 = $this->auditBuilder
            ->auditable($dispatchTruck)
            ->new_values([
                'truck_id' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->division($division)
            ->created_at($date->subHours(4))
            ->create();
        $rec_2 =  $this->auditBuilder
            ->auditable($dispatchEmployer)
            ->new_values([
                'employee_id' => $dispatchEmployer->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->division($division_1)
            ->created_at($date->subHours(3))
            ->create();
        $rec_3 =  $this->auditBuilder
            ->auditable($dispatchEmployer_1)
            ->new_values([
                'employee_id' => $dispatchEmployer_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->created_at($date->subHours(2))
            ->create();

        $res = $this->service->getAuditPaginatorByDispatch(['division_id' => $division->id]);

        $this->assertEquals(count($res->items()), 2);
        $this->assertEquals($res->items()[0]->id, $rec_3->id);
        $this->assertEquals($res->items()[1]->id, $rec_1->id);

        $res = $this->service->getAuditPaginatorByDispatch(['division_id' => $division_1->id]);

        $this->assertEquals(count($res->items()), 2);
        $this->assertEquals($res->items()[0]->id, $rec_3->id);
        $this->assertEquals($res->items()[1]->id, $rec_2->id);
    }

    /** @test */
    public function success_sort()
    {
        $date = CarbonImmutable::now();

        /** @var $work Work */
        $work = $this->workBuilder->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();
        /** @var $dispatchTruck DispatchTruck */
        $dispatchTruck = $this->dispatchTruckBuilder
            ->create();
        $dispatchTruck->audits()->delete();
        /** @var $dispatchEmployer DispatchEmployer */
        $dispatchEmployer = $this->dispatchEmployeeBuilder
            ->create();
        $dispatchEmployer->audits()->delete();
        $dispatchEmployer_1 = $this->dispatchEmployeeBuilder
            ->create();
        $dispatchEmployer_1->audits()->delete();


        $rec_1 = $this->auditBuilder
            ->auditable($dispatchTruck)
            ->new_values([
                'truck_id' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->created_at($date->subHours(4))
            ->create();
        $rec_2 =  $this->auditBuilder
            ->auditable($dispatchEmployer)
            ->new_values([
                'employee_id' => $dispatchEmployer->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->created_at($date->subHours(3))
            ->create();
        $rec_3 =  $this->auditBuilder
            ->auditable($dispatchEmployer_1)
            ->new_values([
                'employee_id' => $dispatchEmployer_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->created_at($date->subHours(2))
            ->create();


        $res = $this->service->getAuditPaginatorByDispatch();

        $this->assertEquals($res->items()[0]->id, $rec_3->id);
        $this->assertEquals($res->items()[1]->id, $rec_2->id);
        $this->assertEquals($res->items()[2]->id, $rec_1->id);

        $res = $this->service->getAuditPaginatorByDispatch(['sort_type' => 'asc']);

        $this->assertEquals($res->items()[0]->id, $rec_1->id);
        $this->assertEquals($res->items()[1]->id, $rec_2->id);
        $this->assertEquals($res->items()[2]->id, $rec_3->id);
    }
}
