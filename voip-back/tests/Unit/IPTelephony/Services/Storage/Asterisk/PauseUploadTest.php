<?php

namespace Tests\Unit\IPTelephony\Services\Storage\Asterisk;

use App\Enums\Formats\DatetimeEnum;
use App\IPTelephony\Entities\Asterisk\QueueLogEntity;
use App\IPTelephony\Services\Storage\Asterisk\QueueLogService;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use App\Models\Sips\Sip;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Asterisk\QueueLogBuilder;
use Tests\Builders\Calls\HistoryBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Reports\ReportBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class PauseUploadTest extends TestCase
{
    use WithFaker;

    private QueueLogService $queueLogService;
    private QueueLogBuilder $queueLogBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected ReportBuilder $reportBuilder;
    protected HistoryBuilder $historyBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queueLogService = $this->app->make(QueueLogService::class);

        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->queueLogBuilder = resolve(QueueLogBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
    }

    /** @test */
    public function upload_paused_data_simple(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date_1 = CarbonImmutable::now()->subMinutes(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_2 = CarbonImmutable::now()->subMinutes(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_3 = CarbonImmutable::now()->subMinutes(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_4 = CarbonImmutable::now()->subMinutes(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(2, $report->pauseItems);

        $this->assertEquals(
            $report->pauseItems[0]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_1->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[0]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_2->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[0]->data);

        $this->assertEquals(
            $report->pauseItems[1]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_3->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[1]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_4->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[1]->data);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    /** @test */
    public function upload_paused_data_several_pause_row(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date_1 = CarbonImmutable::now()->subMinutes(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_1_1 = CarbonImmutable::now()->subMinutes(28);
        $ql_1_1 = $this->queueLogBuilder
            ->setTime($date_1_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_1_2 = CarbonImmutable::now()->subMinutes(27);
        $ql_1_2 = $this->queueLogBuilder
            ->setTime($date_1_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_2 = CarbonImmutable::now()->subMinutes(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_3 = CarbonImmutable::now()->subMinutes(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_3_1 = CarbonImmutable::now()->subMinutes(19);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_3_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_4 = CarbonImmutable::now()->subMinutes(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(2, $report->pauseItems);

        $this->assertEquals(
            $report->pauseItems[0]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_1->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[0]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_2->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[0]->data);

        $this->assertEquals(
            $report->pauseItems[1]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_3->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[1]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_4->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[1]->data);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    /** @test */
    public function upload_paused_data_several_unpause_row(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date_1 = CarbonImmutable::now()->subMinutes(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_2 = CarbonImmutable::now()->subMinutes(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_2_1 = CarbonImmutable::now()->subMinutes(24);
        $ql_2_1 = $this->queueLogBuilder
            ->setTime($date_2_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_3 = CarbonImmutable::now()->subMinutes(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_4 = CarbonImmutable::now()->subMinutes(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_4_1 = CarbonImmutable::now()->subMinutes(13);
        $ql_4_1 = $this->queueLogBuilder
            ->setTime($date_4_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_4_2 = CarbonImmutable::now()->subMinutes(12);
        $ql_4_2 = $this->queueLogBuilder
            ->setTime($date_4_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_4_3 = CarbonImmutable::now()->subMinutes(11);
        $ql_4_3 = $this->queueLogBuilder
            ->setTime($date_4_3)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(2, $report->pauseItems);

        $this->assertEquals(
            $report->pauseItems[0]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_1->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[0]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_2->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[0]->data);

        $this->assertEquals(
            $report->pauseItems[1]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_3->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[1]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_4->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[1]->data);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    /** @test */
    public function upload_paused_data_last_some_pause(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date_1 = CarbonImmutable::now()->subMinutes(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_2 = CarbonImmutable::now()->subMinutes(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_3 = CarbonImmutable::now()->subMinutes(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_4 = CarbonImmutable::now()->subMinutes(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_5 = CarbonImmutable::now()->subMinutes(10);
        $ql_5 = $this->queueLogBuilder
            ->setTime($date_5)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_6 = CarbonImmutable::now()->subMinutes(9);
        $ql_6 = $this->queueLogBuilder
            ->setTime($date_6)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(2, $report->pauseItems);

        $this->assertEquals(
            $report->pauseItems[0]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_1->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[0]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_2->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[0]->data);

        $this->assertEquals(
            $report->pauseItems[1]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_3->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[1]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_4->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[1]->data);

        foreach ($this->queueLogService->getAll() as $rec){
            if($rec->id == $ql_5->id || $rec->id == $ql_6->id){
                $this->assertEquals(0, $rec->is_fetch);
            } else {
                $this->assertEquals(1, $rec->is_fetch);
            }
        }
    }

    /** @test */
    public function upload_paused_data_last_only_some_pause(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date_5 = CarbonImmutable::now()->subMinutes(10);
        $ql_5 = $this->queueLogBuilder
            ->setTime($date_5)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_6 = CarbonImmutable::now()->subMinutes(9);
        $ql_6 = $this->queueLogBuilder
            ->setTime($date_6)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(0, $report->pauseItems);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(0, $rec->is_fetch);
        }
    }

    /** @test */
    public function upload_paused_data_first_some_unpause(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date_1 = CarbonImmutable::now()->subMinutes(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_2 = CarbonImmutable::now()->subMinutes(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_3 = CarbonImmutable::now()->subMinutes(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::PAUSE)
            ->create();

        $date_4 = CarbonImmutable::now()->subMinutes(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(1, $report->pauseItems);

        $this->assertEquals(
            $report->pauseItems[0]->pause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_3->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertEquals(
            $report->pauseItems[0]->unpause_at->format(DatetimeEnum::DEFAULT_FORMAT),
            $date_4->format(DatetimeEnum::DEFAULT_FORMAT)
        );
        $this->assertNotNull($report->pauseItems[0]->data);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    /** @test */
    public function upload_paused_data_first_only_unpause(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $date_1 = CarbonImmutable::now()->subMinutes(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $date_2 = CarbonImmutable::now()->subMinutes(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid(QueueLogEntity::CALLID_NONE)
            ->setAgent($sip->number)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::UNPAUSE)
            ->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(0, $report->pauseItems);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    /** @test */
    public function upload_paused_data_empty(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $this->assertEmpty($report->pauseItems);

        $this->queueLogService->uploadPauseData();

        $report = $employee->report;

        $this->assertCount(0, $report->pauseItems);
    }
}
