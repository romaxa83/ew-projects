<?php

namespace Tests\Unit\Listeners\Kamailio;

use App\Dto\Employees\EmployeeDto;
use App\Events\Employees\EmployeeCreatedEvent;
use App\IPTelephony\Events\Subscriber\SubscriberDeleteEvent;
use App\IPTelephony\Listeners\Subscriber\SubscriberDeleteListeners;
use App\IPTelephony\Listeners\Subscriber\SubscriberInsertListener;
use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class SubscriberDeleteListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected SubscriberService $subscriberService;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->subscriberService = resolve(SubscriberService::class);
    }

    /** @test */
    public function success_delete()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();
        $dto = EmployeeDto::byArgs([
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email->getValue(),
            'department_id' => $employee->department_id,
            'password' => 'password'
        ]);

        $event = new EmployeeCreatedEvent($employee, $dto);
        $listener = resolve(SubscriberInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertTrue($employee->is_insert_kamailio);

        $subscriber = $this->subscriberService->getBy('uuid', $employee->guid);

        $this-> assertNotNull($subscriber);

        $event = new SubscriberDeleteEvent($employee);
        $listener = resolve(SubscriberDeleteListeners::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertFalse($employee->is_insert_kamailio);

        $subscriber = $this->subscriberService->getBy('uuid', $employee->guid);

        $this->assertNull($subscriber);
    }
}
