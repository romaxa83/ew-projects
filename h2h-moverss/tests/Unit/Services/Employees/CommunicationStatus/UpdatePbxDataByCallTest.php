<?php

namespace Tests\Unit\Services\Employees\CommunicationStatus;

use App\Models\Employee;
use App\Models\Zadarma\CallsEvents;
use App\Services\Employees\CommunicationStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Employees\PbxDataBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;
use Zadarma_API\Webhook\AbstractNotify;

class UpdatePbxDataByCallTest extends TestCase
{
    use DatabaseTransactions;

    protected PhoneBuilder $phoneBuilder;
    protected CallEventBuilder $callEventBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected PbxDataBuilder $pbxDataBuilder;
    protected CommunicationStatusService $service;

    public function setUp(): void
    {
        $this->phoneBuilder = resolve(PhoneBuilder::class);
        $this->callEventBuilder = resolve(CallEventBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->pbxDataBuilder = resolve(PbxDataBuilder::class);
        $this->service = resolve(CommunicationStatusService::class);

        parent::setUp();
    }

    /** @test */
    public function success_update_pbx_without_client()
    {
        $ext = 101;
        $pbxId = 373685;

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->create();
        /** @var $pbxData Employee\PbxData */
        $pbxData = $this->pbxDataBuilder
            ->pbx_id($pbxId)
            ->employee($employee)
            ->pbx_ext($ext)
            ->create();
        /** @var $callEvent CallsEvents */
        $callEvent = $this->callEventBuilder
            ->pbx_id($pbxId)
            ->internal($ext)
            ->create();

        $this->assertNull($employee->zadarma_call_rec_id);
        $this->assertNull($pbxData->call_rec_id);
        $this->assertNull($callEvent->client_id);

        $this->service->updatePbxDataByCall($callEvent, AbstractNotify::EVENT_ANSWER);

        $employee->refresh();
        $pbxData->refresh();
        $callEvent->refresh();

        $this->assertEquals($employee->zadarma_call_rec_id, $callEvent->id);
        $this->assertEquals($pbxData->call_rec_id, $callEvent->id);
        $this->assertNull($callEvent->client_id);
    }

    /** @test */
    public function success_update_pbx_with_client()
    {
        $ext = 101;
        $pbxId = 373685;

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->zadarma_sip_status(false)
            ->create();
        /** @var $pbxData Employee\PbxData */
        $pbxData = $this->pbxDataBuilder
            ->pbx_id($pbxId)
            ->employee($employee)
            ->pbx_ext($ext)
            ->create();
        /** @var $callEvent CallsEvents */
        $callEvent = $this->callEventBuilder
            ->pbx_id($pbxId)
            ->internal($ext)
            ->create();

        $phone = $this->phoneBuilder->value($callEvent->destination)->create();

        $this->assertNull($employee->zadarma_call_rec_id);
        $this->assertNull($pbxData->call_rec_id);
        $this->assertNull($callEvent->client_id);

        $this->service->updatePbxDataByCall($callEvent, AbstractNotify::EVENT_ANSWER);

        $employee->refresh();
        $pbxData->refresh();
        $callEvent->refresh();

        $this->assertEquals($employee->zadarma_call_rec_id, $callEvent->id);
        $this->assertEquals($pbxData->call_rec_id, $callEvent->id);
        $this->assertEquals($callEvent->client_id, $phone->client_id);
    }

    /** @test */
    public function success_update_pbx_as_end()
    {
        $ext = 101;
        $pbxId = 373685;

        /** @var $callEvent CallsEvents */
        $callEvent = $this->callEventBuilder
            ->pbx_id($pbxId)
            ->internal($ext)
            ->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->zadarma_call_rec_id($callEvent->id)
            ->create();
        /** @var $pbxData Employee\PbxData */
        $pbxData = $this->pbxDataBuilder
            ->pbx_id($pbxId)
            ->employee($employee)
            ->call_rec_id($callEvent)
            ->pbx_ext($ext)
            ->create();

        $this->assertEquals($employee->zadarma_call_rec_id, $callEvent->id);
        $this->assertEquals($pbxData->call_rec_id, $callEvent->id);


        $this->service->updatePbxDataByCall($callEvent, AbstractNotify::EVENT_END);

        $employee->refresh();
        $pbxData->refresh();

        $this->assertNull($employee->zadarma_call_rec_id);
        $this->assertNull($pbxData->call_rec_id);
    }

    /** @test */
    public function success_update_pbx_as_out_end()
    {
        $ext = 101;
        $pbxId = 373685;

        /** @var $callEvent CallsEvents */
        $callEvent = $this->callEventBuilder
            ->pbx_id($pbxId)
            ->internal($ext)
            ->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->zadarma_call_rec_id($callEvent->id)
            ->create();
        /** @var $pbxData Employee\PbxData */
        $pbxData = $this->pbxDataBuilder
            ->pbx_id($pbxId)
            ->employee($employee)
            ->call_rec_id($callEvent)
            ->pbx_ext($ext)
            ->create();

        $this->assertEquals($employee->zadarma_call_rec_id, $callEvent->id);
        $this->assertEquals($pbxData->call_rec_id, $callEvent->id);


        $this->service->updatePbxDataByCall($callEvent, AbstractNotify::EVENT_OUT_END);

        $employee->refresh();
        $pbxData->refresh();

        $this->assertNull($employee->zadarma_call_rec_id);
        $this->assertNull($pbxData->call_rec_id);
    }
}
