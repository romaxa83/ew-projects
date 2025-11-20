<?php

namespace Tests\Unit\IPTelephony\Services\Storage\Asterisk;

use App\Enums\Reports\ReportStatus;
use App\IPTelephony\Entities\Asterisk\QueueLogEntity;
use App\IPTelephony\Services\Storage\Asterisk\QueueLogService;
use App\Models\Calls\History;
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

class QueueLogServiceTest extends TestCase
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

    // case 1
    /** @test */
    public function upload_rec_has_event_connect_completeagent(): void
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
        /** @var $history History */
        $history = $this->historyBuilder->setData([
            'from_num' => '311',
            'from_name_pretty' => 'test'
        ])->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report = $employee->report;

        $this->assertCount(1, $report->items);
        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report->items[0]->num, '311');
        $this->assertEquals($report->items[0]->wait, '12');
        $this->assertEquals($report->items[0]->total_time, '2');
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_2->format('Y-m-d H:i:s')
        );
        $this->assertEquals($report->items[0]->name, $history->from_name_pretty);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 1
    /** @test */
    public function upload_rec_has_event_connect_completeagent_as_args(): void
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
        /** @var $history History */
        $history = $this->historyBuilder->setData([
            'from_num' => '311',
            'from_name_pretty' => 'test'
        ])->create();

        $callid = $this->faker->uuid;
        $callid_2 = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->create();

        $date_1_2 = CarbonImmutable::now()->subSeconds(30);
        $ql_1_2 = $this->queueLogBuilder
            ->setTime($date_1_2)
            ->setCallid($callid_2)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2_2 = $this->queueLogBuilder
            ->setTime($date_2_2)
            ->setCallid($callid_2)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3_2 = CarbonImmutable::now()->subSeconds(20);
        $ql_3_2 = $this->queueLogBuilder
            ->setTime($date_3_2)
            ->setCallid($callid_2)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData($callid);

        $report = $employee->report;

        $this->assertCount(1, $report->items);
        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report->items[0]->num, '311');
        $this->assertEquals($report->items[0]->wait, '12');
        $this->assertEquals($report->items[0]->total_time, '2');
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_2->format('Y-m-d H:i:s')
        );
        $this->assertEquals($report->items[0]->name, $history->from_name_pretty);

        foreach ($this->queueLogService->getAll() as $rec){
            if($rec->callid == $callid && $rec->time == $date_1){
                $this->assertEquals(1, $rec->is_fetch);
            }
            if($rec->callid == $callid && $rec->time == $date_2){
                $this->assertEquals(1, $rec->is_fetch);
            }
            if($rec->callid == $callid && $rec->time == $date_3){
                $this->assertEquals(1, $rec->is_fetch);
            }
            if($rec->callid == $callid_2 && $rec->time == $date_1_2){
                $this->assertEquals(0, $rec->is_fetch);
            }
            if($rec->callid == $callid_2 && $rec->time == $date_2_2){
                $this->assertEquals(0, $rec->is_fetch);
            }
            if($rec->callid == $callid_2 && $rec->time == $date_3_2){
                $this->assertEquals(0, $rec->is_fetch);
            }

        }
    }

    // case 1
    /** @test */
    public function upload_rec_has_event_connect_completeagent_as_args_not_recs(): void
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
        /** @var $history History */
        $history = $this->historyBuilder->setData([
            'from_num' => '311',
            'from_name_pretty' => 'test'
        ])->create();

        $callid = $this->faker->uuid;
        $callid_2 = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData($callid_2);

        $report = $employee->report;

        $this->assertEmpty($report->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertNull($rec->is_fetch);
        }
    }

    // case 1
    /** @test */
    public function upload_rec_has_event_connect_completcaller(): void
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
        /** @var $history History */
        $history = $this->historyBuilder->setData([
            'from_num' => '311',
            'from_name_pretty' => null
        ])->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETECALLER)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report->refresh();

        $this->assertEquals($report->employee_id, $employee->id);

        $this->assertCount(1, $report->items);
        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report->items[0]->num, '311');
        $this->assertEquals($report->items[0]->wait, '12');
        $this->assertEquals($report->items[0]->total_time, '2');
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_2->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 1
    /** @test */
    public function upload_rec_has_event_connect_completcaller_not_found_sip(): void
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
        /** @var $history History */
        $history = $this->historyBuilder->setData([
            'from_num' => '311',
            'from_name_pretty' => null
        ])->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent('300')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETECALLER)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $this->assertEmpty($report->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(2, $rec->is_fetch);
        }
    }

    /** @test */
    public function not_upload_rec_has_event_connect_not_completcaller(): void
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

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report->refresh();

        $this->assertEmpty($report->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(2, $rec->is_fetch);
        }
    }

    // case 2
    /** @test */
    public function upload_rec_has_event_connect_blindtransfer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '312'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::BLINDTRANSFER)
            ->setData1('312')
            ->setData3('10')
            ->setData4('11')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report = $employee->report;

        $this->assertCount(1, $report->items);
        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::TRANSFER);
        $this->assertEquals($report->items[0]->num, '311');
        $this->assertEquals($report->items[0]->wait, $ql_3->data3);
        $this->assertEquals($report->items[0]->total_time, '11');
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_2->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 2
    /** @test */
    public function upload_rec_has_event_connect_blindtransfer_not_found_sip(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '312'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent('300')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::BLINDTRANSFER)
            ->setData1('312')
            ->setData3('10')
            ->setData4('11')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $this->assertEmpty($report->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(2, $rec->is_fetch);
        }
    }

    // case 4
    /** @test */
    public function upload_rec_has_event_ringnoanswer_connect_completecaller(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '312'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('1000')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('11')
            ->setData3('4')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETECALLER)
            ->setData1('11')
            ->setData2('15')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $report_2->refresh();

        $this->assertEmpty($report->items);
        $this->assertCount(1, $report_2->items);

        $this->assertEquals($report_2->items[0]->callid, $callid);
        $this->assertEquals($report_2->items[0]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report_2->items[0]->num, '+107537102');
        $this->assertEquals($report_2->items[0]->wait, $ql_4->data1);
        $this->assertEquals($report_2->items[0]->total_time, $ql_4->data2);
        $this->assertEquals(
            $report_2->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_4->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_2->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 4
    /** @test */
    public function upload_rec_has_event_ringnoanswer_connect_completeagent(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '312'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('20000')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('11')
            ->setData3('4')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('11')
            ->setData2('15')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $report_2->refresh();

        $this->assertCount(1, $report->items);
        $this->assertCount(1, $report_2->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 20);
        $this->assertEquals($report->items[0]->total_time, 0);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_2->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        $this->assertEquals($report_2->items[0]->callid, $callid);
        $this->assertEquals($report_2->items[0]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report_2->items[0]->num, '+107537102');
        $this->assertEquals($report_2->items[0]->wait, $ql_4->data1);
        $this->assertEquals($report_2->items[0]->total_time, $ql_4->data2);
        $this->assertEquals(
            $report_2->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_4->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_2->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 4
    /** @test */
    public function upload_rec_has_event_ringnoanswer_connect_completeagent_not_found_sip(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '312'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent('300')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('20000')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('11')
            ->setData3('4')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('11')
            ->setData2('15')
            ->setData3('1')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        $this->queueLogService->uploadData();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(2, $rec->is_fetch);
        }
    }

    // case 5
    /** @test */
    public function upload_rec_has_event_ringnoanswer_ringcanceled_abadon(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('0')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('0')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGCANCELED)
            ->setData1('20000')
            ->create();

        $date_5 = CarbonImmutable::now()->subSeconds(14);
        $ql_5 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ABANDON)
            ->setData1('1')
            ->setData1('1')
            ->setData1('20')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report->refresh();

        $this->assertCount(1, $report->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 20);
        $this->assertEquals($report->items[0]->total_time, 0);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_4->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 5
    /** @test */
    public function upload_rec_has_event_ringnoanswer_ringcanceled_abadon_has_some_recs(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '311'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('0')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('10000')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGCANCELED)
            ->setData1('20000')
            ->create();

        $date_5 = CarbonImmutable::now()->subSeconds(14);
        $ql_5 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ABANDON)
            ->setData1('1')
            ->setData1('1')
            ->setData1('20')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $report_2->refresh();

        $this->assertCount(1, $report->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 10);
        $this->assertEquals($report->items[0]->total_time, 0);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_3->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        $this->assertCount(1, $report_2->items);

        $this->assertEquals($report_2->items[0]->callid, $callid);
        $this->assertEquals($report_2->items[0]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report_2->items[0]->num, '+107537102');
        $this->assertEquals($report_2->items[0]->wait, 20);
        $this->assertEquals($report_2->items[0]->total_time, 0);
        $this->assertEquals(
            $report_2->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_4->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_2->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 5
    /** @test */
    public function upload_rec_has_event_some_ringnoanswer_abadon_save(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('10000')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('0')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('10000')
            ->create();

        $date_5 = CarbonImmutable::now()->subSeconds(14);
        $ql_5 = $this->queueLogBuilder
            ->setTime($date_5)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('10000')
            ->create();

        $date_6 = CarbonImmutable::now()->subSeconds(15);
        $ql_6 = $this->queueLogBuilder
            ->setTime($date_6)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ABANDON)
            ->setData1('1')
            ->setData1('1')
            ->setData1('20')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report->refresh();

        $this->assertCount(3, $report->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 10);
        $this->assertEquals($report->items[0]->total_time, 0);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_2->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        $this->assertEquals($report->items[1]->callid, $callid);
        $this->assertEquals($report->items[1]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report->items[1]->num, '+107537102');
        $this->assertEquals($report->items[1]->wait, 10);
        $this->assertEquals($report->items[1]->total_time, 0);
        $this->assertEquals(
            $report->items[1]->call_at->format('Y-m-d H:i:s'),
            $date_4->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[1]->name);

        $this->assertEquals($report->items[2]->callid, $callid);
        $this->assertEquals($report->items[2]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report->items[2]->num, '+107537102');
        $this->assertEquals($report->items[2]->wait, 10);
        $this->assertEquals($report->items[2]->total_time, 0);
        $this->assertEquals(
            $report->items[2]->call_at->format('Y-m-d H:i:s'),
            $date_5->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[2]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 6
    /** @test */
    public function upload_rec_has_event_connect_blindtransfer_ringnoanswer_completagent(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '311'])->create();
        $sip_3 = $this->sipBuilder->setData(['number' => '312'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        $employee_3 = $this->employeeBuilder->setSip($sip_3)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();
        $report_3 = $this->reportBuilder->setEmployee($employee_3)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('13')
            ->setData3('5')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::BLINDTRANSFER)
            ->setData3('13')
            ->setData4('153')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2($sip->number)
            ->create();

        $date_5 = CarbonImmutable::now()->subSeconds(14);
        $ql_5 = $this->queueLogBuilder
            ->setTime($date_5)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('1000')
            ->create();

        $date_6 = CarbonImmutable::now()->subSeconds(13);
        $ql_6 = $this->queueLogBuilder
            ->setTime($date_6)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('23')
            ->setData2('4')
            ->create();

        $date_7 = CarbonImmutable::now()->subSeconds(12);
        $ql_7 = $this->queueLogBuilder
            ->setTime($date_7)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::BLINDTRANSFER)
            ->setData3('14')
            ->setData4('147')
            ->create();

        $date_8 = CarbonImmutable::now()->subSeconds(11);
        $ql_8 = $this->queueLogBuilder
            ->setTime($date_8)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2($sip_2->number)
            ->create();

        $date_9 = CarbonImmutable::now()->subSeconds(10);
        $ql_9 = $this->queueLogBuilder
            ->setTime($date_9)
            ->setCallid($callid)
            ->setAgent($sip_3)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('10000')
            ->create();

        $date_10 = CarbonImmutable::now()->subSeconds(9);
        $ql_10 = $this->queueLogBuilder
            ->setTime($date_10)
            ->setCallid($callid)
            ->setAgent($sip_3)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('25')
            ->setData2('6')
            ->create();

        $date_11 = CarbonImmutable::now()->subSeconds(8);
        $ql_11 = $this->queueLogBuilder
            ->setTime($date_11)
            ->setCallid($callid)
            ->setAgent($sip_3)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('25')
            ->setData2('609')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);
        $this->assertEmpty($report_3->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $report_2->refresh();
        $report_3->refresh();

        $this->assertCount(1, $report->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::TRANSFER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 13);
        $this->assertEquals($report->items[0]->total_time, 153);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_3->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        $this->assertCount(1, $report_2->items);

        $this->assertEquals($report_2->items[0]->callid, $callid);
        $this->assertEquals($report_2->items[0]->status, ReportStatus::TRANSFER);
        $this->assertEquals($report_2->items[0]->num, '+107537102');
        $this->assertEquals($report_2->items[0]->wait, 14);
        $this->assertEquals($report_2->items[0]->total_time, 147);
        $this->assertEquals(
            $report_2->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_7->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_2->items[0]->name);

        $this->assertCount(2, $report_3->items);

        $this->assertEquals($report_3->items[0]->callid, $callid);
        $this->assertEquals($report_3->items[0]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report_3->items[0]->num, '+107537102');
        $this->assertEquals($report_3->items[0]->wait, 10);
        $this->assertEquals($report_3->items[0]->total_time, 0);
        $this->assertEquals(
            $report_3->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_9->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_3->items[0]->name);

        $this->assertEquals($report_3->items[1]->callid, $callid);
        $this->assertEquals($report_3->items[1]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report_3->items[1]->num, '+107537102');
        $this->assertEquals($report_3->items[1]->wait, 25);
        $this->assertEquals($report_3->items[1]->total_time, 609);
        $this->assertEquals(
            $report_3->items[1]->call_at->format('Y-m-d H:i:s'),
            $date_11->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_3->items[1]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 6
    /** @test */
    public function upload_rec_has_event_connect_blindtransfer_ringnoanswer_completcaller(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '311'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('13')
            ->setData3('5')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::BLINDTRANSFER)
            ->setData3('13')
            ->setData4('113')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2($sip->number)
            ->create();

        $date_5 = CarbonImmutable::now()->subSeconds(14);
        $ql_5 = $this->queueLogBuilder
            ->setTime($date_5)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('4000')
            ->create();

        $date_6 = CarbonImmutable::now()->subSeconds(13);
        $ql_6 = $this->queueLogBuilder
            ->setTime($date_6)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('23')
            ->setData2('4')
            ->create();

        $date_11 = CarbonImmutable::now()->subSeconds(8);
        $ql_11 = $this->queueLogBuilder
            ->setTime($date_11)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETECALLER)
            ->setData1('25')
            ->setData2('609')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $report_2->refresh();

        $this->assertCount(1, $report->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::TRANSFER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 13);
        $this->assertEquals($report->items[0]->total_time, 113);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_3->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        $this->assertCount(1, $report_2->items);

        $this->assertEquals($report_2->items[0]->callid, $callid);
        $this->assertEquals($report_2->items[0]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report_2->items[0]->num, '+107537102');
        $this->assertEquals($report_2->items[0]->wait, 25);
        $this->assertEquals($report_2->items[0]->total_time, 609);
        $this->assertEquals(
            $report_2->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_11->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_2->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 6
    /** @test */
    public function upload_rec_has_event_connect_blindtransfer_completcaller(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '311'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('13')
            ->setData3('5')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::BLINDTRANSFER)
            ->setData3('13')
            ->setData4('43')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2($sip->number)
            ->create();

        $date_6 = CarbonImmutable::now()->subSeconds(13);
        $ql_6 = $this->queueLogBuilder
            ->setTime($date_6)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('23')
            ->setData2('4')
            ->create();

        $date_11 = CarbonImmutable::now()->subSeconds(8);
        $ql_11 = $this->queueLogBuilder
            ->setTime($date_11)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETECALLER)
            ->setData1('25')
            ->setData2('609')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $report_2->refresh();

        $this->assertCount(1, $report->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::TRANSFER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 13);
        $this->assertEquals($report->items[0]->total_time, 43);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_3->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        $this->assertCount(1, $report_2->items);

        $this->assertEquals($report_2->items[0]->callid, $callid);
        $this->assertEquals($report_2->items[0]->status, ReportStatus::ANSWERED);
        $this->assertEquals($report_2->items[0]->num, '+107537102');
        $this->assertEquals($report_2->items[0]->wait, 25);
        $this->assertEquals($report_2->items[0]->total_time, 609);
        $this->assertEquals(
            $report_2->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_11->format('Y-m-d H:i:s')
        );
        $this->assertNull($report_2->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 6
    /** @test */
    public function upload_rec_has_event_connect_blindtransfer_completcaller_not_found_sip(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '311'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();
        $report_2 = $this->reportBuilder->setEmployee($employee_2)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent('300')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('13')
            ->setData3('5')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent('300')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::BLINDTRANSFER)
            ->setData3('13')
            ->setData4('43')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2($sip->number)
            ->create();

        $date_6 = CarbonImmutable::now()->subSeconds(13);
        $ql_6 = $this->queueLogBuilder
            ->setTime($date_6)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('23')
            ->setData2('4')
            ->create();

        $date_11 = CarbonImmutable::now()->subSeconds(8);
        $ql_11 = $this->queueLogBuilder
            ->setTime($date_11)
            ->setCallid($callid)
            ->setAgent($sip_2)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETECALLER)
            ->setData1('25')
            ->setData2('609')
            ->create();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $report_2->refresh();

        $this->assertEmpty($report->items);
        $this->assertEmpty($report_2->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(2, $rec->is_fetch);
        }
    }

    // case 5
    /** @test */
    public function upload_rec_has_event_ringcanceled_abandon_has_recs(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGCANCELED)
            ->setData1('10990')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ABANDON)
            ->setData1('1')
            ->setData2('1')
            ->setData3('17')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report->refresh();

        $this->assertCount(1, $report->items);

        $this->assertEquals($report->items[0]->callid, $callid);
        $this->assertEquals($report->items[0]->status, ReportStatus::NO_ANSWER);
        $this->assertEquals($report->items[0]->num, '+107537102');
        $this->assertEquals($report->items[0]->wait, 10);
        $this->assertEquals($report->items[0]->total_time, 0);
        $this->assertEquals(
            $report->items[0]->call_at->format('Y-m-d H:i:s'),
            $date_2->format('Y-m-d H:i:s')
        );
        $this->assertNull($report->items[0]->name);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 5
    /** @test */
    public function upload_rec_has_event_ringcanceled_abandon_no_recs(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGCANCELED)
            ->setData1('990')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ABANDON)
            ->setData1('1')
            ->setData2('1')
            ->setData3('17')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $this->assertEmpty($report->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    // case 5
    /** @test */
    public function upload_rec_ignore_not_found_sip(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '310'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($employee)->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('+107537102')
            ->setData3('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent('300')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('20000')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent('300')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::RINGNOANSWER)
            ->setData1('20000')
            ->create();

        $date_4 = CarbonImmutable::now()->subSeconds(15);
        $ql_4 = $this->queueLogBuilder
            ->setTime($date_4)
            ->setCallid($callid)
            ->setAgent('NONE')
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ABANDON)
            ->setData1('1')
            ->setData2('1')
            ->setData3('17')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $report->refresh();
        $this->assertEmpty($report->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(2, $rec->is_fetch);
        }
    }

    /** @test */
    public function not_upload_if_fetch(): void
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
        /** @var $history History */
        $history = $this->historyBuilder->setData([
            'from_num' => '311',
            'from_name_pretty' => 'test'
        ])->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->setFetch('1')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->setFetch('1')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->setFetch('1')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $this->assertEmpty($report->items);
    }

    /** @test */
    public function not_upload_if_fetch_as_ignore(): void
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
        /** @var $history History */
        $history = $this->historyBuilder->setData([
            'from_num' => '311',
            'from_name_pretty' => 'test'
        ])->create();

        $callid = $this->faker->uuid;

        $date_1 = CarbonImmutable::now()->subSeconds(30);
        $ql_1 = $this->queueLogBuilder
            ->setTime($date_1)
            ->setCallid($callid)
            ->setAgent(QueueLogEntity::CALLID_NONE)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::ENTERQUEUE)
            ->setData2('311')
            ->setData3('1')
            ->setFetch('2')
            ->create();

        $date_2 = CarbonImmutable::now()->subSeconds(25);
        $ql_2 = $this->queueLogBuilder
            ->setTime($date_2)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::CONNECT)
            ->setData1('12')
            ->setData2($this->faker->uuid)
            ->setData3('4')
            ->setFetch('2')
            ->create();

        $date_3 = CarbonImmutable::now()->subSeconds(20);
        $ql_3 = $this->queueLogBuilder
            ->setTime($date_3)
            ->setCallid($callid)
            ->setAgent($sip)
            ->setQueuename($department)
            ->setEvent(QueueLogEntity::COMPLETEAGENT)
            ->setData1('12')
            ->setData2('2')
            ->setData3('1')
            ->setFetch('2')
            ->create();

        $this->assertEmpty($report->items);

        $this->queueLogService->uploadData();

        $this->assertEmpty($report->items);
    }
}

