<?php

namespace Tests\Unit\IPTelephony\Services\Storage\Kamailio;

use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Mockery;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class SubscriberServiceTest extends TestCase
{
    private SubscriberService $service;
    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SubscriberService::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function transform_data_for_insert(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $data = $this->service->prepareDataForInsertSubscriber($employee);

        $this->assertEquals(data_get($data, 'username'), $sip->number);
        $this->assertEquals(data_get($data, 'domain'), config('kamailio.domain'));
        $this->assertEquals(data_get($data, 'context'), config('kamailio.context'));
        $this->assertEquals(data_get($data, 'password'), $sip->password);
        $this->assertEquals(data_get($data, 'uuid'), $employee->guid);
        $this->assertEquals(data_get($data, 'full_name'), $employee->getName());
    }

//    /** @test */
//    public function success_insert(): void
//    {
//        /** @var $sip Sip */
//        $sip = $this->sipBuilder->create();
//        /** @var $employee Employee */
//        $employee = $this->employeeBuilder->setSip($sip)->create();
//
//        $this->assertFalse($employee->is_insert_kamailio);
//
//
//        $mockService = Mockery::mock(new KamailioService);
//        $this->app->instance(KamailioService::class, $mockService);
//        $mockService->shouldReceive('remove')
//            ->once()
//            ->andReturn(true);
//
//        $this->service->create($employee);
//
//        $employee->refresh();
//
//        $this->assertTrue($employee->is_insert_kamailio);
//    }
}


