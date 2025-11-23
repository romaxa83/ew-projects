<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForDispatch;

use App\Models\Audit;
use App\Models\DispatchEmployer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Order\Work;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Employees\DispatchEmployeeBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\TestCase;

class ByEmployeeTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected WorkBuilder $workBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected DispatchEmployeeBuilder $dispatchEmployeeBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->dispatchEmployeeBuilder = resolve(DispatchEmployeeBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);


        parent::setUp();
    }

    /** @test */
    public function audit_dispatch_employee_add_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();

        /** @var $model DispatchEmployer */
        $model = $this->dispatchEmployeeBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'employee_id' => $employee->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'employee');
        $this->assertEquals($res[0]['details'][0]['new'], $employee->full_name);
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], "Dispatch employee (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }

    /** @test */
    public function audit_dispatch_employees_change_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        $employee_1 = $this->employeeBuilder->create();

        /** @var $model DispatchEmployer */
        $model = $this->dispatchEmployeeBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_UPDATED)
            ->new_values([
                'employee_ids' => $employee->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->old_values([
                'employee_ids' => $employee_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'employee');
        $this->assertEquals($res[0]['details'][0]['new'], $employee->full_name);
        $this->assertEquals($res[0]['details'][0]['old'], $employee_1->full_name);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Dispatch employee (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }

    /** @test */
    public function audit_dispatch_employees_more_change_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        $employee_1 = $this->employeeBuilder->create();

        /** @var $model DispatchEmployer */
        $model = $this->dispatchEmployeeBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_UPDATED)
            ->new_values([
                'employee_ids' => $employee->id .','. $employee_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->old_values([
                'employee_ids' => $employee_1->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'employee');
        $this->assertEquals($res[0]['details'][0]['new'], $employee->full_name.', '.$employee_1->full_name);
        $this->assertEquals($res[0]['details'][0]['old'], $employee_1->full_name);

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], "Dispatch employee (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }

    /** @test */
    public function audit_dispatch_trucks_remove_to_calendar()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $work Work */
        $work = $this->workBuilder->order($order)->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();

        /** @var $model DispatchEmployer */
        $model = $this->dispatchEmployeeBuilder->work($work)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($model)
            ->event(Audit::EVENT_DELETED)
            ->old_values([
                'employee_ids' => $employee->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service
            ->forDispatch($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'employee');
        $this->assertNull($res[0]['details'][0]['new']);
        $this->assertEquals($res[0]['details'][0]['old'], $employee->full_name);

        $this->assertEquals($res[0]['action'], Audit::EVENT_DELETED);
        $this->assertEquals($res[0]['entity'], "Dispatch employee (by order - #{$order->id})");
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
    }
}
