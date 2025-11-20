<?php

namespace Tests\Unit\Listeners\Asterisk\QueueMember;

use App\IPTelephony\Events\QueueMember\QueueMemberUpdateEvent;
use App\IPTelephony\Listeners\QueueMember\QueueMemberUpdateOrInsertListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\Models\Departments\Department;
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

class QueueMemberUpdateOrInsertListenerTest extends TestCase
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

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
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
    public function success_update_department_name()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_queue);

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertTrue($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);
        $this->assertNotNull($queueMember);

        $department->update(['name' => $department->name . '_update']);
        $employee->refresh();

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
        $listener->handle($event);

//        $this->assertTrue($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this->assertEquals($queueMember->queue_name, $department->name);
    }

    /** @test */
    public function success_update_sip_name()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sipAnother = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)
            ->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_queue);

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertTrue($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);
        $this->assertNotNull($queueMember);

        $employee->update(['sip_id' => $sipAnother->id]);
        $employee->refresh();

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
        $listener->handle($event);

//        $this->assertTrue($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this->assertEquals($queueMember->membername, $sipAnother->number);
        $this->assertEquals($queueMember->interface, 'Local/' . $sipAnother->number . '@queue_members');
        $this->assertEquals($queueMember->state_interface, 'Custom:' . $sipAnother->number);
    }


    public function fail_insert()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_queue);

        $mockQueueMemberService = Mockery::mock(new QueueMemberService());
        $this->app->instance(QueueMemberService::class, $mockQueueMemberService);
        $mockQueueMemberService->shouldReceive('editOrCreate')
            ->once()
            ->andThrow(Exception::class);

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

//        $this->assertFalse($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

//        $this-> assertNull($queueMember);
    }


    public function fail_update()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sipAnother = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_queue);

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertTrue($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this->assertNotNull($queueMember);

        $employee->update(['sip_id' => $sipAnother->id]);
        $employee->refresh();

        $mockQueueMemberService = Mockery::mock(new QueueMemberService());
        $this->app->instance(QueueMemberService::class, $mockQueueMemberService);
        $mockQueueMemberService->shouldReceive('editOrCreate')
            ->once()
            ->andThrow(Exception::class);

        $event = new QueueMemberUpdateEvent($employee);
        $listener = resolve(QueueMemberUpdateOrInsertListener::class);
        $listener->handle($event);

//        $this->assertTrue($employee->is_insert_queue);

        $queueMember = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this->assertNotEquals($queueMember->membername, $sipAnother->number);
    }
}
