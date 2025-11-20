<?php

namespace Tests\Unit\Listeners\Asterisk\QueueMember;

use App\IPTelephony\Events\QueueMember\QueueMemberUpdateNameEvent;
use App\IPTelephony\Listeners\QueueMember\QueueMemberUpdateNameListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Builders\Asterisk\QueueMemberBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueMemberUpdateNameListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected QueueMemberService $queueMemberService;
    protected QueueMemberBuilder $queueMemberBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->queueMemberService = resolve(QueueMemberService::class);
        $this->queueMemberBuilder = resolve(QueueMemberBuilder::class);
    }

    /** @test */
    public function success_update_department_names()
    {
        $newName = 'new_dept_name';
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setSip($sip)->create();
        $employee_2 = $this->employeeBuilder
            ->setDepartment($department)
            ->setSip($sip_2)->create();

        $q_1 = $this->queueMemberBuilder->setEmployee($employee)->create();
        $q_2 = $this->queueMemberBuilder->setEmployee($employee_2)->create();

        $this->assertNotEquals($q_1->queue_name, $newName);
        $this->assertNotEquals($q_2->queue_name, $newName);

        $event = new QueueMemberUpdateNameEvent($department->name, $newName);
        $listener = resolve(QueueMemberUpdateNameListener::class);
        $listener->handle($event);

        $q_1_up = $this->queueMemberService->getBy('uuid', $q_1->uuid);
        $q_2_up = $this->queueMemberService->getBy('uuid', $q_2->uuid);

        $this->assertEquals($q_1_up->queue_name, $newName);
        $this->assertEquals($q_2_up->queue_name, $newName);
    }

    /** @test */
    public function fail_update()
    {
        $newName = 'new_dept_name';
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setDepartment($department)
            ->setSip($sip)->create();

        $q_1 = $this->queueMemberBuilder->setEmployee($employee)->create();

        $this->assertNotEquals($q_1->queue_name, $newName);

        $mockQueueMemberService = Mockery::mock(new QueueMemberService());
        $this->app->instance(QueueMemberService::class, $mockQueueMemberService);
        $mockQueueMemberService->shouldReceive('updateQueueNames')
            ->once()
            ->andThrow(Exception::class);

        $event = new QueueMemberUpdateNameEvent($department->name, $newName);
        $listener = resolve(QueueMemberUpdateNameListener::class);
        $listener->handle($event);

        $q_1_up = $this->queueMemberService->getBy('uuid', $q_1->uuid);

        $this->assertNotEquals($q_1_up->queue_name, $newName);
    }
}

