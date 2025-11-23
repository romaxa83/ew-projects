<?php

namespace Tests\Unit\Services\Employees\CommunicationStatus;

use App\Services\Employees\CommunicationStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class UpdateEmployeesFromRingostatTest extends TestCase
{
    use DatabaseTransactions;
    protected EmployeeBuilder $employeeBuilder;
    protected CommunicationStatusService $service;

    public function setUp(): void
    {
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->service = resolve(CommunicationStatusService::class);

        parent::setUp();
    }

    /** @test */
    public function success_update()
    {
        $employee_1 = $this->employeeBuilder->ringostat_id(1)->create();
        $employee_2 = $this->employeeBuilder->ringostat_id(2)->create();
        $employee_3 = $this->employeeBuilder->ringostat_id(3)->create();

        $data = [
            [
                'id' => $employee_1->ringostat_id,
                'status' => true,
            ],
            [
                'id' => $employee_2->ringostat_id,
                'status' => true,
            ],
            [
                'id' => $employee_3->ringostat_id,
                'status' => false,
            ]
        ];

        $this->assertFalse($employee_1->ringostat_sip_status);
        $this->assertFalse($employee_2->ringostat_sip_status);
        $this->assertFalse($employee_3->ringostat_sip_status);

        $this->service->updateEmployeesFromRingostat($data);

        $employee_1->refresh();
        $employee_2->refresh();
        $employee_3->refresh();

        $this->assertTrue($employee_1->ringostat_sip_status);
        $this->assertTrue($employee_2->ringostat_sip_status);
        $this->assertFalse($employee_3->ringostat_sip_status);
    }
}

