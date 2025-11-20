<?php

namespace Tests\Unit\Listeners\Asterisk\QueueMember;

use App\Events\Employees\EmployeeCreatedEvent;
use App\IPTelephony\Listeners\QueueMember\QueueMemberInsertListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueMemberInsertListenerTest extends TestCase
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
    public function success_insert()
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

        $this->assertEquals($queueMember->queue_name, $employee->department->name);
        $this->assertEquals($queueMember->uuid, $employee->guid);
        $this->assertEquals($queueMember->membername, $sip->number);
        $this->assertEquals($queueMember->interface, 'Local/' . $sip->number . '@queue_members');
        $this->assertEquals($queueMember->state_interface, 'Custom:' . $sip->number);
        $this->assertEquals($queueMember->wrapuptime, config('asterisk.queue_member.wrapuptime'));
        $this->assertEquals($queueMember->ringinuse, config('asterisk.queue_member.ringinuse'));
    }

    /** @test */
    public function fail_insert()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_queue);

        $mockQueueMemberService = Mockery::mock(new QueueMemberService());
        $this->app->instance(QueueMemberService::class, $mockQueueMemberService);
        $mockQueueMemberService->shouldReceive('create')
            ->once()
            ->andThrow(Exception::class);

        $event = new EmployeeCreatedEvent($employee);
        $listener = resolve(QueueMemberInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertFalse($employee->is_insert_kamailio);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this-> assertNull($queueMember);
    }
}
