<?php

namespace Tests\Unit\Commands\Workers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Kamailio\LocationBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class ExistSipToLocationKamailioTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected LocationBuilder $locationBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->locationBuilder = resolve(LocationBuilder::class);
    }

    /** @test */
    public function check_sip_to_location(): void
    {
        $sip_1 = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        $sip_3 = $this->sipBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->create();
        $employee_3 = $this->employeeBuilder->setSip($sip_3)->create();
        $employee_4 = $this->employeeBuilder->create();

        $this->locationBuilder->setSip($sip_1)->create();
        $this->locationBuilder->setSip($sip_2)->create();

        $this->assertFalse($employee_1->status->isError());
        $this->assertFalse($employee_2->status->isError());
        $this->assertFalse($employee_3->status->isError());
        $this->assertFalse($employee_4->status->isError());

        $this->artisan('workers:exist_sip_to_location');

        $employee_1->refresh();
        $employee_2->refresh();
        $employee_3->refresh();
        $employee_4->refresh();

        $this->assertFalse($employee_1->status->isError());
        $this->assertFalse($employee_2->status->isError());
        $this->assertTrue($employee_3->status->isError());
        $this->assertTrue($employee_4->status->isError());
    }
}
