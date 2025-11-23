<?php

namespace Tests\Unit\Services\Employees\CommunicationStatus;

use App\Services\Employees\CommunicationStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Employees\PbxDataBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;

class UpdateEmployeesFromPbxDataTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeBuilder $employeeBuilder;
    protected PbxDataBuilder $pbxDataBuilder;
    protected CallEventBuilder $callEventBuilder;
    protected CommunicationStatusService $service;

    public function setUp(): void
    {
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->pbxDataBuilder = resolve(PbxDataBuilder::class);
        $this->callEventBuilder = resolve(CallEventBuilder::class);
        $this->service = resolve(CommunicationStatusService::class);

        parent::setUp();
    }

    /** @test */
    public function success_update_employee_from_pbx_as_true()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $employee_1 = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->sip_status(true)
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->sip_status(false)
            ->create();

        $this->assertFalse($employee_1->zadarma_sip_status);
        $this->assertNull($employee_1->zadarma_call_rec_id);
        $this->assertTrue($pbxData_1->sip_status);
        $this->assertNull($pbxData_1->call_rec_id);
        $this->assertFalse($pbxData_2->sip_status);
        $this->assertNull($pbxData_2->call_rec_id);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertTrue($employee_1->zadarma_sip_status);
        $this->assertNull($employee_1->zadarma_call_rec_id);
    }

    /** @test */
    public function success_update_employee_from_pbx_as_true_second()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $employee_1 = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->sip_status(false)
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->sip_status(true)
            ->create();

        $this->assertFalse($employee_1->zadarma_sip_status);
        $this->assertTrue($pbxData_2->sip_status);
        $this->assertFalse($pbxData_1->sip_status);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertTrue($employee_1->zadarma_sip_status);
    }

    /** @test */
    public function success_update_employee_from_pbx_as_false()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $employee_1 = $this->employeeBuilder
            ->zadarma_sip_status(true)
            ->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->sip_status(false)
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->sip_status(false)
            ->create();

        $this->assertTrue($employee_1->zadarma_sip_status);
        $this->assertFalse($pbxData_2->sip_status);
        $this->assertFalse($pbxData_1->sip_status);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertFalse($employee_1->zadarma_sip_status);
    }

    /** @test */
    public function not_change_employee_from_pbx_as_true()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $employee_1 = $this->employeeBuilder
            ->zadarma_sip_status(true)
            ->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->sip_status(false)
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->sip_status(true)
            ->create();

        $this->assertTrue($employee_1->zadarma_sip_status);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertTrue($employee_1->zadarma_sip_status);
    }

    /** @test */
    public function not_change_employee_from_pbx_as_false()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $employee_1 = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->sip_status(false)
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->sip_status(false)
            ->create();

        $this->assertFalse($employee_1->zadarma_sip_status);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertFalse($employee_1->zadarma_sip_status);
    }

    /** @test */
    public function has_call_rec_id()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $employee_1 = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->create();

        $call = $this->callEventBuilder->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->call_rec_id($call)
            ->sip_status(false)
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->sip_status(false)
            ->create();

        $this->assertFalse($employee_1->zadarma_sip_status);
        $this->assertNull($employee_1->call_rec_id);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertFalse($employee_1->zadarma_sip_status);
        $this->assertEquals($employee_1->zadarma_call_rec_id, $call->id);
    }

    /** @test */
    public function has_call_rec_id_second()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $employee_1 = $this->employeeBuilder
            ->zadarma_sip_status(true)
            ->create();

        $call = $this->callEventBuilder->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->sip_status(false)
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->call_rec_id($call)
            ->sip_status(true)
            ->create();

        $this->assertTrue($employee_1->zadarma_sip_status);
        $this->assertNull($employee_1->call_rec_id);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertTrue($employee_1->zadarma_sip_status);
        $this->assertEquals($employee_1->zadarma_call_rec_id, $call->id);
    }

    /** @test */
    public function remove_call_rec_id()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $call = $this->callEventBuilder->create();

        $employee_1 = $this->employeeBuilder
            ->zadarma_call_rec_id($call->id)
            ->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->create();

        $this->assertEquals($employee_1->zadarma_call_rec_id, $call->id);


        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertNull($employee_1->call_rec_id);
    }

    /** @test */
    public function not_change_call_rec_id()
    {
        $pbx_id_1 = '373685';
        $pbx_id_2 = '373682';
        $numbers = [
            101,102
        ];

        $call = $this->callEventBuilder->create();

        $employee_1 = $this->employeeBuilder
            ->zadarma_call_rec_id($call->id)
            ->create();

        $pbxData_1 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_1)
            ->employee($employee_1)
            ->pbx_ext($numbers[0])
            ->create();
        $pbxData_2 = $this->pbxDataBuilder
            ->pbx_id($pbx_id_2)
            ->employee($employee_1)
            ->pbx_ext($numbers[1])
            ->call_rec_id($call)
            ->create();

        $this->assertEquals($employee_1->zadarma_call_rec_id, $call->id);

        $this->service->updateEmployeesFromPbxData();

        $employee_1->refresh();

        $this->assertEquals($employee_1->zadarma_call_rec_id, $call->id);
    }
}
