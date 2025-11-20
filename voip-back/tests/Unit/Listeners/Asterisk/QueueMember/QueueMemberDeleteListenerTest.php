<?php

namespace Tests\Unit\Listeners\Asterisk\QueueMember;

use App\Events\Employees\EmployeeCreatedEvent;
use App\IPTelephony\Events\Subscriber\SubscriberDeleteEvent;
use App\IPTelephony\Listeners\QueueMember\QueueMemberDeleteListener;
use App\IPTelephony\Listeners\QueueMember\QueueMemberInsertListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueMemberDeleteListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected QueueMemberService $queueMemberService;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->queueMemberService = resolve(QueueMemberService::class);
    }

    /** @test */
    public function success_delete()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_queue);

        $event = new EmployeeCreatedEvent($employee);
        $listener = resolve(QueueMemberInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertTrue($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this->assertNotNull($queueMember);

        $event = new SubscriberDeleteEvent($employee);
        $listener = resolve(QueueMemberDeleteListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertFalse($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this->assertNull($queueMember);
    }
}
