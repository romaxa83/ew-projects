<?php

namespace Tests\Unit\Listeners\Kamailio;

use App\Dto\Employees\EmployeeDto;
use App\Events\Employees\EmployeeCreatedEvent;
use App\IPTelephony\Listeners\Subscriber\SubscriberInsertListener;
use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;
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

class SubscriberInsertListenerTest extends TestCase
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
    public function success_insert()
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

        $this->assertFalse($employee->is_insert_kamailio);

        $event = new EmployeeCreatedEvent($employee, $dto);
        $listener = resolve(SubscriberInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertTrue($employee->is_insert_kamailio);

        $subscriber = $this->subscriberService->getBy('uuid', $employee->guid);

        $this->assertEquals($subscriber->username, $sip->number);
        $this->assertEquals($subscriber->domain, config('kamailio.domain'));
        $this->assertEquals($subscriber->password, $sip->password);
        $this->assertEquals($subscriber->context, config('kamailio.context'));
        $this->assertEquals($subscriber->uuid, $employee->guid);
        $this->assertEquals($subscriber->full_name, $employee->getName());
    }

    /** @test */
    public function fail_insert()
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

        $mockSubscriberService = Mockery::mock(new SubscriberService);
        $this->app->instance(SubscriberService::class, $mockSubscriberService);
        $mockSubscriberService->shouldReceive('create')
            ->once()
            ->andThrow(Exception::class);

        $event = new EmployeeCreatedEvent($employee, $dto);
        $listener = resolve(SubscriberInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertFalse($employee->is_insert_kamailio);

        $subscriber = $this->subscriberService->getBy('uuid', $employee->guid);

        $this-> assertNull($subscriber);
    }
}
