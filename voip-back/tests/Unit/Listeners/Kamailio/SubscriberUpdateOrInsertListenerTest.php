<?php

namespace Tests\Unit\Listeners\Kamailio;

use App\IPTelephony\Events\Subscriber\SubscriberUpdateOrCreateEvent;
use App\IPTelephony\Listeners\Subscriber\SubscriberUpdateOrInsertListener;
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

class SubscriberUpdateOrInsertListenerTest extends TestCase
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

        $this->assertFalse($employee->is_insert_kamailio);

        $event = new SubscriberUpdateOrCreateEvent($employee);
        $listener = resolve(SubscriberUpdateOrInsertListener::class);
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
    public function success_update()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sipAnother = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_kamailio);

        $event = new SubscriberUpdateOrCreateEvent($employee);
        $listener = resolve(SubscriberUpdateOrInsertListener::class);
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

        $employee->update(['sip_id' => $sipAnother->id]);
        $employee->refresh();

        $event = new SubscriberUpdateOrCreateEvent($employee);
        $listener = resolve(SubscriberUpdateOrInsertListener::class);
        $listener->handle($event);

        $this->assertTrue($employee->is_insert_kamailio);

        $subscriber = $this->subscriberService->getBy('uuid', $employee->guid);

        $this->assertEquals($subscriber->username, $sipAnother->number);
        $this->assertEquals($subscriber->domain, config('kamailio.domain'));
        $this->assertEquals($subscriber->password, $sipAnother->password);
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

        $this->assertFalse($employee->is_insert_kamailio);

        $mockSubscriberService = Mockery::mock(new SubscriberService);
        $this->app->instance(SubscriberService::class, $mockSubscriberService);
        $mockSubscriberService->shouldReceive('editOrCreate')
            ->once()
            ->andThrow(Exception::class);

        $event = new SubscriberUpdateOrCreateEvent($employee);
        $listener = resolve(SubscriberUpdateOrInsertListener::class);
        $listener->handle($event);

        $employee->refresh();

        $this->assertFalse($employee->is_insert_kamailio);

        $subscriber = $this->subscriberService->getBy('uuid', $employee->guid);

        $this-> assertNull($subscriber);
    }

    /** @test */
    public function fail_update()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sipAnother = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->assertFalse($employee->is_insert_kamailio);

        $event = new SubscriberUpdateOrCreateEvent($employee);
        $listener = resolve(SubscriberUpdateOrInsertListener::class);
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

        $employee->update(['sip_id' => $sipAnother->id]);
        $employee->refresh();

        $mockSubscriberService = Mockery::mock(new SubscriberService);
        $this->app->instance(SubscriberService::class, $mockSubscriberService);
        $mockSubscriberService->shouldReceive('editOrCreate')
            ->once()
            ->andThrow(Exception::class);

        $event = new SubscriberUpdateOrCreateEvent($employee);
        $listener = resolve(SubscriberUpdateOrInsertListener::class);
        $listener->handle($event);

        $this->assertTrue($employee->is_insert_kamailio);

        $subscriber = $this->subscriberService->getBy('uuid', $employee->guid);

        $this->assertEquals($subscriber->username, $sip->number);
        $this->assertEquals($subscriber->domain, config('kamailio.domain'));
        $this->assertEquals($subscriber->password, $sip->password);
        $this->assertEquals($subscriber->context, config('kamailio.context'));
        $this->assertEquals($subscriber->uuid, $employee->guid);
        $this->assertEquals($subscriber->full_name, $employee->getName());
    }
}
