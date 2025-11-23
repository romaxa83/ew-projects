<?php

namespace Tests\Unit\Services\Employees\CommunicationStatus;

use App\Services\Employees\CommunicationStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Employees\PbxDataBuilder;
use Tests\TestCase;
use Zadarma_API\Response\PbxStatus;

class UpdateEmployeesPbxDataFromZadarmaTest extends TestCase
{
    use DatabaseTransactions;

    protected EmployeeBuilder $employeeBuilder;
    protected PbxDataBuilder $pbxDataBuilder;
    protected CommunicationStatusService $service;

    public function setUp(): void
    {
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->pbxDataBuilder = resolve(PbxDataBuilder::class);
        $this->service = resolve(CommunicationStatusService::class);

        parent::setUp();
    }

    /** @test */
    public function success_update_pbx()
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
        $this->assertFalse($pbxData_1->sip_status);
        $this->assertFalse($pbxData_2->sip_status);

        $clientStub = $this->createStub(\Zadarma_API\Api::class);
        $clientStub->method('getPbxStatus')
            ->will($this->returnCallback(function ($id) use ($numbers, $pbx_id_1) {
                switch ($id) {
                    case $numbers[0]:
                        return new PbxStatus([
                            'pbx_id' => $pbx_id_1,
                            'number' => $numbers[0],
                            'is_online' => 'true'
                        ]);
                    case $numbers[1]:
                        return new PbxStatus([
                            'pbx_id' => $pbx_id_1,
                            'number' => $numbers[1],
                            'is_online' => 'true'
                        ]);
                    default:
                        return ['status' => 'unknown', 'id' => $id];
                }
            }));

        $this->service->updateEmployeesPbxDataFromZadarma($clientStub, $numbers, $pbx_id_1);

        $employee_1->refresh();
        $pbxData_1->refresh();
        $pbxData_2->refresh();

        $this->assertFalse($employee_1->zadarma_sip_status);
        $this->assertTrue($pbxData_1->sip_status);
        $this->assertFalse($pbxData_2->sip_status);
    }
}
