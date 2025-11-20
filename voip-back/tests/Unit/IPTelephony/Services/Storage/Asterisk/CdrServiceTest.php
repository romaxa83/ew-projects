<?php

namespace Tests\Unit\IPTelephony\Services\Storage\Asterisk;

use App\Enums\Calls\HistoryStatus;
use App\Enums\Calls\QueueStatus;
use App\IPTelephony\Entities\Asterisk\CdrEntity;
use App\IPTelephony\Entities\Asterisk\QueueLogEntity;
use App\IPTelephony\Services\Storage\Asterisk\CdrService;
use App\IPTelephony\Services\Storage\Asterisk\QueueLogService;
use App\Models\Calls\History;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use App\Models\Sips\Sip;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Asterisk\CdrBuilder;
use Tests\Builders\Asterisk\QueueLogBuilder;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Reports\ReportBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class CdrServiceTest extends TestCase
{
    use WithFaker;

    private CdrService $cdrService;
    private QueueLogService $queueLogService;
    private CdrBuilder $cdrBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected QueueBuilder $queueBuilder;
    protected ReportBuilder $reportBuilder;

    protected QueueLogBuilder $queueLogBuilder;


    protected function setUp(): void
    {
        parent::setUp();

        $this->cdrService = $this->app->make(CdrService::class);
        $this->queueLogService = $this->app->make(QueueLogService::class);

        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->cdrBuilder = resolve(CdrBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);

        $this->queueLogBuilder = resolve(QueueLogBuilder::class);
    }

    /** @test */
    public function upload_cdr_one_record(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $sn = '344356346UYIRFVJK';
        $case = '3443';

        $cdr = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setSerialNumber($sn)
            ->setClid("\"Customer Support\" <Ira>")
            ->setCaseId($case)
            ->create();

        $this->assertNull(
            History::query()
            ->where('call_date', $cdr->calldate)
            ->where('uniqueid', $cdr->uniqueid)
            ->first()
        );
        $this->assertNull($cdr->is_fetch);

        $this->cdrService->uploadCdrData();

        $history = History::query()
            ->where('call_date', $cdr->calldate)
            ->where('uniqueid', $cdr->uniqueid)
            ->first();

        $this->assertEquals($history->employee_id, $employee->id);
        $this->assertEquals($history->department_id, $department->id);
        $this->assertEquals($history->status->value, mb_strtolower($cdr->disposition));
        $this->assertEquals($history->from_name, $cdr->clid);
        $this->assertEquals($history->from_name_pretty, 'Customer Support');
        $this->assertEquals($history->from_num, $cdr->src);
        $this->assertEquals($history->dialed, $cdr->dst);
        $this->assertEquals($history->dialed_name, $employee->getName());
        $this->assertEquals($history->duration, $cdr->duration);
        $this->assertEquals($history->billsec, $cdr->billsec);
        $this->assertEquals($history->serial_numbers, $sn);
        $this->assertEquals($history->case_id, $case);
        $this->assertEquals($history->lastapp, $cdr->lastapp);
        $this->assertEquals($history->uniqueid, $cdr->uniqueid);
        $this->assertEquals($history->call_date, $cdr->calldate);
        $this->assertEquals($history->channel, $cdr->channel);
        $this->assertNull($history->comment);

        $updateCdr = $this->cdrService->getByFields([
            'calldate' => $cdr->calldate,
            'uniqueid' => $cdr->uniqueid,
        ]);

        $this->assertEquals(1, $updateCdr->is_fetch);
    }

    /** @test */
    public function upload_cdr_one_record_with_report(): void
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

        $sn = '344356346UYIRFVJK';
        $case = '3443';

        $callid = $this->faker->uuid;

        $cdr = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setSerialNumber($sn)
            ->setClid("\"Customer Support\" <Ira>")
            ->setCaseId($case)
            ->setUniqueid($callid)
            ->create();

        // данные для отчета
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

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
        $this->assertNull($cdr->is_fetch);

        $this->cdrService->uploadCdrData();

        $history = History::query()
            ->where('call_date', $cdr->calldate)
            ->where('uniqueid', $cdr->uniqueid)
            ->first();

        $this->assertEquals($history->employee_id, $employee->id);
        $this->assertEquals($history->department_id, $department->id);
        $this->assertEquals($history->status->value, mb_strtolower($cdr->disposition));
        $this->assertEquals($history->from_name, $cdr->clid);
        $this->assertEquals($history->from_name_pretty, 'Customer Support');
        $this->assertEquals($history->from_num, $cdr->src);
        $this->assertEquals($history->dialed, $cdr->dst);
        $this->assertEquals($history->dialed_name, $employee->getName());
        $this->assertEquals($history->duration, $cdr->duration);
        $this->assertEquals($history->billsec, $cdr->billsec);
        $this->assertEquals($history->serial_numbers, $sn);
        $this->assertEquals($history->case_id, $case);
        $this->assertEquals($history->lastapp, $cdr->lastapp);
        $this->assertEquals($history->uniqueid, $cdr->uniqueid);
        $this->assertEquals($history->call_date, $cdr->calldate);
        $this->assertEquals($history->channel, $cdr->channel);
        $this->assertNull($history->comment);

        $updateCdr = $this->cdrService->getByFields([
            'calldate' => $cdr->calldate,
            'uniqueid' => $cdr->uniqueid,
        ]);

        $this->assertEquals(1, $updateCdr->is_fetch);

        $report = $employee->report;

        $this->assertCount(1, $report->items);

        foreach ($this->queueLogService->getAll() as $rec){
            $this->assertEquals(1, $rec->is_fetch);
        }
    }

    /** @test */
    public function upload_cdr_one_record_with_from_employee_cancel(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();

        $cdr = $this->cdrBuilder
            ->setSrc($sip_2->number)
            ->setDst($sip->number)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDisposition(CdrEntity::STATUS_NO_ANSWER)
            ->setTrueReasonHangup(CdrEntity::STATUS_CANCEL)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setFromEmployee($employee_2)
            ->create();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
        $this->assertNull($cdr->is_fetch);

        $this->cdrService->uploadCdrData();

        $history = History::query()
            ->where('call_date', $cdr->calldate)
            ->where('uniqueid', $cdr->uniqueid)
            ->first();

        $this->assertEquals($history->employee_id, $employee->id);
        $this->assertEquals($history->from_employee_id, $employee_2->id);
        $this->assertEquals($history->department_id, $department->id);
        $this->assertEquals($history->status->value, HistoryStatus::CANCEL);

        $updateCdr = $this->cdrService->getByFields([
            'calldate' => $cdr->calldate,
            'uniqueid' => $cdr->uniqueid,
        ]);

        $this->assertEquals(1, $updateCdr->is_fetch);
    }

    /** @test */
    public function upload_cdr_one_record_with_from_employee_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();

        $cdr = $this->cdrBuilder
            ->setSrc($sip_2->number)
            ->setDst($sip->number)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDisposition(CdrEntity::STATUS_NO_ANSWER)
            ->setTrueReasonHangup(null)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setFromEmployee($employee_2)
            ->create();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
        $this->assertNull($cdr->is_fetch);

        $this->cdrService->uploadCdrData();

        $history = History::query()
            ->where('call_date', $cdr->calldate)
            ->where('uniqueid', $cdr->uniqueid)
            ->first();

        $this->assertEquals($history->employee_id, $employee->id);
        $this->assertEquals($history->from_employee_id, $employee_2->id);
        $this->assertEquals($history->department_id, $department->id);
        $this->assertEquals($history->status->value, HistoryStatus::NO_ANSWER);

        $updateCdr = $this->cdrService->getByFields([
            'calldate' => $cdr->calldate,
            'uniqueid' => $cdr->uniqueid,
        ]);

        $this->assertEquals(1, $updateCdr->is_fetch);
    }

    /** @test */
    public function upload_cdr_some_records(): void
    {
        $channel = 'PJSIP/kamailio-00000f3a';
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();

        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setCaseId('5656')
            ->setSerialNumber('5656RYDYGRTER')
            ->setComment('comment')
            ->create();

        $cdr_1 = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();
        $cdr_2 = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $cdr_3 = $this->cdrBuilder
            ->setSrc($sip_2->number)
            ->setDepartment($department)
            ->setChannel($channel)
            ->create();

        $this->assertNull($cdr_3->case_id);
        $this->assertNull($cdr_3->serial);

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(3, History::query()->count());

        $history_3 = History::query()
            ->where('call_date', $cdr_3->calldate)
            ->where('uniqueid', $cdr_3->uniqueid)
            ->first();



        $this->assertNull($history_3->employee_id);
        $this->assertEquals($history_3->department_id, $department->id);
        $this->assertEquals($history_3->status->value, mb_strtolower($cdr_3->disposition));
        $this->assertEquals($history_3->from_name, $cdr_3->clid);
        $this->assertEquals($history_3->from_num, $cdr_3->src);
        $this->assertEquals($history_3->dialed, $cdr_3->dst);
        $this->assertNull($history_3->dialed_name);
        $this->assertEquals($history_3->duration, $cdr_3->duration);
        $this->assertEquals($history_3->billsec, $cdr_3->billsec);
        $this->assertEquals($history_3->serial_numbers, $queue->serial_number);
        $this->assertEquals($history_3->comment, $queue->comment);
        $this->assertEquals($history_3->case_id, $queue->case_id);
        $this->assertEquals($history_3->lastapp, $cdr_3->lastapp);
        $this->assertEquals($history_3->uniqueid, $cdr_3->uniqueid);
        $this->assertEquals($history_3->call_date, $cdr_3->calldate);
    }

    // case 1
    /** @test */
    public function upload_cdr_transfer_to_agent_no_answer(): void
    {
        $channel = 'PJSIP/kamailio-00000f3a';
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_1 = $this->employeeBuilder
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setSerialNumber('5656RYDYGRTER3')
            ->setCaseId('5656RYDYGRTER')
            ->setComment(null)
            ->create();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('2002')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setSerialNumber('78787')
            ->setChannel($channel)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('2002')
            ->setLastData('PJSIP/2000@kamailio,60,g')
            ->setDuration(2)
            ->setUniqueid($uniqID)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CHANUNAVAIL')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('2002')
            ->setLastData('')
            ->setDuration(0)
            ->setUniqueid($uniqID)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertNull($cdr_1->case_id);

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_1->dialed, '3000');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertNull($rec_1->comment);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;
        $this->assertEquals($rec_2->from_num, $cdr_2->src);
        $this->assertEquals($rec_2->from_name, $cdr_2->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::CHANUNAVAIL);
        $this->assertEquals($rec_2->dialed, '2002');
        $this->assertEquals($rec_2->duration, $cdr_2->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_2->serial);
        $this->assertEquals($rec_2->case_id, $cdr_2->case_id);
        $this->assertEquals($rec_2->lastapp, $cdr_2->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 2
    /** @test */
    public function upload_cdr_transfer_to_agent_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_1 = $this->employeeBuilder
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('2002')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('2002')
            ->setLastData('PJSIP/2000@kamailio,60,g')
            ->setDuration(2)
            ->setUniqueid($uniqID)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('ANSWER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('2002')
            ->setLastData('')
            ->setDuration(0)
            ->setUniqueid($uniqID)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_1->dialed, '3000');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;
        $this->assertEquals($rec_2->from_num, $cdr_2->src);
        $this->assertEquals($rec_2->from_name, $cdr_2->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::ANSWERED);
        $this->assertEquals($rec_2->dialed, '2002');
        $this->assertEquals($rec_2->duration, $cdr_2->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_2->serial);
        $this->assertEquals($rec_2->case_id, $cdr_2->case_id);
        $this->assertEquals($rec_2->lastapp, $cdr_2->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 3
    /** @test */
    public function upload_cdr_dial_transfer_answer_has_hangup(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('314')
            ->setLastData('PJSIP/311@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('312')
            ->setLastData('PJSIP/314@kamailio,60,g')
            ->setDuration(2)
            ->setUniqueid($uniqID)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setLastData('PJSIP/312@kamailio,60,g')
            ->setDst('312')
            ->setDuration(5)
            ->setUniqueid($uniqID)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_4 = $date->subSeconds(10);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('ANSWER')
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('312')
            ->setLastData('')
            ->setUniqueid($uniqID)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(3, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '310');
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_1->dialed, '311');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_2->from_num, '310');
        $this->assertEquals($rec_2->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_2->dialed, '314');
        $this->assertEquals($rec_2->duration, $cdr_2->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_2->serial);
        $this->assertEquals($rec_2->case_id, $cdr_2->case_id);
        $this->assertEquals($rec_2->from_name, $cdr_2->clid);
        $this->assertEquals($rec_2->lastapp, $cdr_2->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $rec_3 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_3->from_num, '310');
        $this->assertEquals($rec_3->status->value, HistoryStatus::ANSWERED);
        $this->assertEquals($rec_3->dialed, '312');
        $this->assertEquals($rec_3->duration, $cdr_3->duration);
        $this->assertEquals($rec_3->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_3->serial_numbers, $cdr_3->serial);
        $this->assertEquals($rec_3->case_id, $cdr_3->case_id);
        $this->assertEquals($rec_3->from_name, $cdr_3->clid);
        $this->assertEquals($rec_3->lastapp, $cdr_3->lastapp);
        $this->assertEquals($rec_3->employee_id, $employee->id);
        $this->assertEquals($rec_3->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 3
    /** @test */
    public function upload_cdr_dial_transfer_answer_no_hangup(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('314')
            ->setLastData('PJSIP/311@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('312')
            ->setLastData('PJSIP/314@kamailio,60,g')
            ->setDuration(2)
            ->setUniqueid($uniqID)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setLastData('PJSIP/312@kamailio,60,g')
            ->setDst('312')
            ->setDuration(5)
            ->setUniqueid($uniqID)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(3, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '310');
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_1->dialed, '311');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_2->from_num, '310');
        $this->assertEquals($rec_2->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_2->dialed, '314');
        $this->assertEquals($rec_2->duration, $cdr_2->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_2->serial);
        $this->assertEquals($rec_2->case_id, $cdr_2->case_id);
        $this->assertEquals($rec_2->from_name, $cdr_2->clid);
        $this->assertEquals($rec_2->lastapp, $cdr_2->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $rec_3 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_3->from_num, '310');
        $this->assertEquals($rec_3->status->value, HistoryStatus::ANSWERED);
        $this->assertEquals($rec_3->dialed, '312');
        $this->assertEquals($rec_3->duration, $cdr_3->duration);
        $this->assertEquals($rec_3->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_3->serial_numbers, $cdr_3->serial);
        $this->assertEquals($rec_3->case_id, $cdr_3->case_id);
        $this->assertEquals($rec_3->from_name, $cdr_3->clid);
        $this->assertEquals($rec_3->lastapp, $cdr_3->lastapp);
        $this->assertEquals($rec_3->employee_id, $employee->id);
        $this->assertEquals($rec_3->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 3
    /** @test */
    public function upload_cdr_dial_transfer_no_answer_has_hangup(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_1 = $this->employeeBuilder
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('314')
            ->setLastData('PJSIP/311@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('312')
            ->setLastData('PJSIP/314@kamailio,60,g')
            ->setDuration(2)
            ->setUniqueid($uniqID)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setLastData('PJSIP/312@kamailio,60,g')
            ->setDst('312')
            ->setDuration(5)
            ->setUniqueid($uniqID)
            ->setSrc('310')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_4 = $date->subSeconds(10);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CHANUNAVAIL')
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('2003')
            ->setLastData('')
            ->setDuration(88)
            ->setUniqueid($uniqID)
            ->setSrc('1001')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(3, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '310');
        $this->assertEquals($rec_1->dialed, '311');
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;
        $this->assertEquals($rec_2->from_num, '310');
        $this->assertEquals($rec_2->dialed, '314');
        $this->assertEquals($rec_2->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_2->duration, $cdr_2->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec);
        $this->assertEquals($rec_2->from_name, $cdr_2->clid);
        $this->assertEquals($rec_2->serial_numbers, $cdr_2->serial);
        $this->assertEquals($rec_2->case_id, $cdr_2->case_id);
        $this->assertEquals($rec_2->lastapp, $cdr_2->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $rec_3 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID)
            ->first();

        $this->assertEquals($rec_3->from_num, '310');
        $this->assertEquals($rec_3->dialed, '312');
        $this->assertEquals($rec_3->from_name, $cdr_3->clid);
        $this->assertEquals($rec_3->status->value, HistoryStatus::CHANUNAVAIL);
        $this->assertEquals($rec_3->duration, $cdr_3->duration);
        $this->assertEquals($rec_3->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_3->serial_numbers, $cdr_3->serial);
        $this->assertEquals($rec_3->case_id, $cdr_3->case_id);
        $this->assertEquals($rec_3->lastapp, $cdr_3->lastapp);
        $this->assertEquals($rec_3->employee_id, $employee->id);
        $this->assertEquals($rec_3->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 3
    /** @test */
    public function upload_cdr_some_dial_has_transfer_and_from_employee(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        $sip_3 = $this->sipBuilder->create();
        $sip_4 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        $employee_3 = $this->employeeBuilder->setSip($sip_3)
            ->setDepartment($department)->create();
        $employee_4 = $this->employeeBuilder->setSip($sip_4)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDisposition(CdrEntity::STATUS_ANSWERED)
            ->setTrueReasonHangup(null)
            ->setCallDate($date_1)
            ->setLastData("PJSIP/{$employee_2->sip->number}@kamailio,60,g")
            ->setDepartment($department)
            ->setEmployee($employee_2)
            ->setFromEmployee($employee)
            ->setUniqueid($uniqID)
            ->setDuration(5)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDisposition(CdrEntity::STATUS_ANSWERED)
            ->setTrueReasonHangup(CdrEntity::STATUS_TRANSFER)
            ->setCallDate($date_2)
            ->setLastData("PJSIP/{$employee_3->sip->number}@kamailio,60,g")
            ->setDepartment($department)
            ->setEmployee($employee_3)
            ->setFromEmployee($employee)
            ->setUniqueid($uniqID)
            ->setDuration(25)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDisposition(CdrEntity::STATUS_ANSWERED)
            ->setTrueReasonHangup(CdrEntity::STATUS_TRANSFER)
            ->setCallDate($date_3)
            ->setLastData("PJSIP/{$employee_4->sip->number}@kamailio,60,g")
            ->setDepartment($department_2)
            ->setEmployee($employee_4)
            ->setFromEmployee($employee)
            ->setUniqueid($uniqID)
            ->setDuration(58)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(3, $recs);

        $this->assertEquals($recs[0]->from_num, $employee->sip->number);
        $this->assertEquals($recs[0]->dialed, $employee_2->sip->number);
        $this->assertEquals($recs[0]->dialed_name, $employee_2->getName());
        $this->assertEquals($recs[0]->employee_id, $employee_2->id);
        $this->assertEquals($recs[0]->from_employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);
        $this->assertEquals($recs[0]->status->value, mb_strtolower(CdrEntity::STATUS_TRANSFER));
        $this->assertEquals($recs[0]->duration, $cdr_1->duration);

        $this->assertEquals($recs[1]->from_num, $employee->sip->number);
        $this->assertEquals($recs[1]->dialed, $employee_3->sip->number);
        $this->assertEquals($recs[1]->dialed_name, $employee_3->getName());
        $this->assertEquals($recs[1]->employee_id, $employee_3->id);
        $this->assertEquals($recs[1]->from_employee_id, $employee->id);
        $this->assertEquals($recs[1]->department_id, $department->id);
        $this->assertEquals($recs[1]->status->value, mb_strtolower(CdrEntity::STATUS_TRANSFER));
        $this->assertEquals($recs[1]->duration, $cdr_2->duration);

        $this->assertEquals($recs[2]->from_num, $employee->sip->number);
        $this->assertEquals($recs[2]->dialed, $employee_4->sip->number);
        $this->assertEquals($recs[2]->dialed_name, $employee_4->getName());
        $this->assertEquals($recs[2]->employee_id, $employee_4->id);
        $this->assertEquals($recs[2]->from_employee_id, $employee->id);
        $this->assertEquals($recs[2]->department_id, $department_2->id);
        $this->assertEquals($recs[2]->status->value, mb_strtolower(CdrEntity::STATUS_ANSWERED));
        $this->assertEquals($recs[2]->duration, $cdr_3->duration);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 15, схож с case 3, только без hangup
    /** @test */
    public function upload_cdr_transfer_to_agent_without_hangup_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('admin')
            ->setLastData('PJSIP/311@kamailio,60,g')
            ->setDuration(11)
            ->setSrc('310')
            ->setDst('312')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('PJSIP/kamailio-0000021f')
            ->setLastData('PJSIP/312@kamailio,60,g')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(0)
            ->setSrc('310')
            ->setDst('312')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '310');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($rec_1->dialed, '311');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->lastapp, CdrEntity::TYPE_DIAL);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_2->from_num, '310');
        $this->assertEquals($rec_2->from_name, $cdr_1->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($rec_2->dialed, '312');
        $this->assertEquals($rec_2->duration, $cdr_2->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec);
        $this->assertEquals($rec_2->lastapp, CdrEntity::TYPE_DIAL);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    /** @test */
    public function upload_cdr_transfer_to_some_agents_answer_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('20000')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('1001')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('2')
            ->setLastData('20000,,,,600')
            ->setDstchannel('Local/2002@queue_members-000001d9;1')
            ->setDuration(2)
            ->setUniqueid($uniqID)
            ->setSrc('300')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setLastData('20000,,,,600')
            ->setDstchannel('Local/2002@queue_members-000001d9;1')
            ->setDst('2')
            ->setDuration(5)
            ->setUniqueid($uniqID)
            ->setSrc('3000')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_4 = $date->subSeconds(15);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setLastData('20000,,,,600')
            ->setDstchannel('Local/2002@queue_members-000001d9;1')
            ->setDst('2')
            ->setDuration(88)
            ->setUniqueid($uniqID)
            ->setSrc('3000')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_5 = $date->subSeconds(10);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('ANSWER')
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setLastData('20000,,,,600')
            ->setDstchannel('PJSIP/kamailio-0000201a')
            ->setDst('2')
            ->setDuration(23)
            ->setUniqueid($uniqID)
            ->setSrc('3000')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_1->dialed, '3000');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_5)
            ->where('uniqueid', $uniqID)
            ->first()
        ;
        $this->assertEquals($rec_2->from_num, $cdr_5->src);
        $this->assertEquals($rec_2->from_name, $cdr_5->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::ANSWERED);
        $this->assertEquals($rec_2->dialed, '2002');
        $this->assertEquals($rec_2->duration, $cdr_2->duration + $cdr_3->duration + $cdr_4->duration + $cdr_5->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec + $cdr_3->billsec + $cdr_4->billsec + $cdr_5->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_5->serial);
        $this->assertEquals($rec_2->case_id, $cdr_5->case_id);
        $this->assertEquals($rec_2->lastapp, $cdr_5->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    /** @test */
    public function upload_cdr_transfer_to_some_agents_no_answer_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('20000')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('1001')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('2')
            ->setLastData('20000,,,,600')
            ->setDstchannel('Local/2002@queue_members-000001d9;1')
            ->setDuration(2)
            ->setUniqueid($uniqID)
            ->setSrc('300')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setLastData('20000,,,,600')
            ->setDstchannel('Local/2002@queue_members-000001d9;1')
            ->setDst('2')
            ->setDuration(5)
            ->setUniqueid($uniqID)
            ->setSrc('3000')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_4 = $date->subSeconds(15);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setLastData('20000,,,,600')
            ->setDstchannel('Local/2002@queue_members-000001d9;1')
            ->setDst('2')
            ->setDuration(88)
            ->setUniqueid($uniqID)
            ->setSrc('3000')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_5 = $date->subSeconds(10);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CHANUNAVAIL')
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setLastData('20000,,,,600')
            ->setDstchannel('PJSIP/kamailio-0000201a')
            ->setDst('2')
            ->setDuration(23)
            ->setUniqueid($uniqID)
            ->setSrc('3000')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($rec_1->dialed, '3000');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_5)
            ->where('uniqueid', $uniqID)
            ->first()
        ;
        $this->assertEquals($rec_2->from_num, $cdr_5->src);
        $this->assertEquals($rec_2->from_name, $cdr_5->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::CHANUNAVAIL);
        $this->assertEquals($rec_2->dialed, '2002');
        $this->assertEquals($rec_2->duration, $cdr_2->duration + $cdr_3->duration + $cdr_4->duration + $cdr_5->duration);
        $this->assertEquals($rec_2->billsec, $cdr_2->billsec + $cdr_3->billsec + $cdr_4->billsec + $cdr_5->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_5->serial);
        $this->assertEquals($rec_2->case_id, $cdr_5->case_id);
        $this->assertEquals($rec_2->lastapp, $cdr_5->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    /** @test */
    public function upload_cdr_missed_call_queue_all_answered(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000217;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDstchannel('PJSIP/kamailio-00002229')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 8
    /** @test */
    public function upload_cdr_missed_call_queue_first_not_answered(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107810936')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDstchannel('Local/310@queue_members-00000222;1')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('+107810936')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDstchannel('PJSIP/kamailio-00002229')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('+107810936')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration + $cdr_3->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_2->billsec + $cdr_2->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 9
    /** @test */
    public function upload_cdr_missed_call_queue_not_answered(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(3)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(27)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $date_4 = $date->subSeconds(15);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(10)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration + $cdr_3->duration + $cdr_4->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_2->billsec + $cdr_3->billsec + $cdr_4->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertNull($rec_1->employee_id);
        $this->assertEquals($rec_1->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 9
    /** @test */
    public function upload_cdr_missed_call_queue_not_answered_has_hangup(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(3)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(27)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $date_4 = $date->subSeconds(15);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(10)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $date_5 = $date->subSeconds(10);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDstchannel('Local/310@queue_members-00000221;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(0)
            ->setBillsec(0)
            ->setSrc('311')
            ->setDepartment($department)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration + $cdr_3->duration + $cdr_4->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_2->billsec + $cdr_3->billsec + $cdr_4->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertNull($rec_1->employee_id);
        $this->assertEquals($rec_1->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 7.1
    /** @test */
    public function upload_cdr_queue_transfer_to_dial_and_dial_fail(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000021d;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('311')
            ->setDst('1')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('312')
            ->setLastData('Sales Department_test,,,,600')
            ->setDstchannel('PJSIP/kamailio-0000223e')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('1')
            ->setLastData('PJSIP/312@kamailio,60,g')
            ->setDstchannel('PJSIP/kamailio-0000223f')
            ->setDuration(1)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_4 = $date->subSeconds(20);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CHANUNAVAIL')
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('1')
            ->setLastData('')
            ->setDstchannel('')
            ->setDuration(0)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_2->from_num, '311');
        $this->assertEquals($rec_2->from_name, $cdr_3->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::CHANUNAVAIL());
        $this->assertEquals($rec_2->dialed, '312');
        $this->assertEquals($rec_2->duration, $cdr_3->duration);
        $this->assertEquals($rec_2->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_3->serial);
        $this->assertEquals($rec_2->case_id, $cdr_3->case_id);
        $this->assertEquals($rec_2->lastapp, CdrEntity::TYPE_DIAL);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 7.2
    /** @test */
    public function upload_cdr_queue_transfer_to_dial_and_dial_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000021d;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('311')
            ->setDst('1')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('312')
            ->setLastData('Sales Department_test,,,,600')
            ->setDstchannel('PJSIP/kamailio-0000223e')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('1')
            ->setLastData('PJSIP/312@kamailio,60,g')
            ->setDstchannel('PJSIP/kamailio-0000223f')
            ->setDuration(1)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_2->from_num, '311');
        $this->assertEquals($rec_2->from_name, $cdr_3->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($rec_2->dialed, '312');
        $this->assertEquals($rec_2->duration, $cdr_3->duration);
        $this->assertEquals($rec_2->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_3->serial);
        $this->assertEquals($rec_2->case_id, $cdr_3->case_id);
        $this->assertEquals($rec_2->lastapp, CdrEntity::TYPE_DIAL);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 7.3
    /** @test */
    public function upload_cdr_queue_transfer_to_dial_and_dial_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000021d;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('311')
            ->setDst('1')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('312')
            ->setLastData('Sales Department_test,,,,600')
            ->setDstchannel('PJSIP/kamailio-0000223e')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('1')
            ->setLastData('PJSIP/312@kamailio,60,g')
            ->setDstchannel('PJSIP/kamailio-0000223f')
            ->setDuration(1)
            ->setUniqueid($uniqID)
            ->setSrc('311')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_2->from_num, '311');
        $this->assertEquals($rec_2->from_name, $cdr_3->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($rec_2->dialed, '312');
        $this->assertEquals($rec_2->duration, $cdr_3->duration);
        $this->assertEquals($rec_2->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_3->serial);
        $this->assertEquals($rec_2->case_id, $cdr_3->case_id);
        $this->assertEquals($rec_2->lastapp, CdrEntity::TYPE_DIAL);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 7.4
    /** @test */
    public function upload_cdr_queue_some_transfer_to_dial_and_dial_answer(): void
    {
        /** @var $sip_1 Sip */
        $sip_1 = $this->sipBuilder->setData(['number' => '320'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '321'])->create();
        $sip_3 = $this->sipBuilder->setData(['number' => '322'])->create();
        /** @var $department_1 Department */
        $department_1 = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();
        $department_3 = $this->departmentBuilder->create();
        /** @var $employee_1 Employee */
        $employee_1 = $this->employeeBuilder->setSip($sip_1)
            ->setDepartment($department_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();
        $employee_3 = $this->employeeBuilder->setSip($sip_3)
            ->setDepartment($department_3)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-000000f5;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(20)
            ->setSrc('+10762027392')
            ->setDst('1')
            ->setDepartment($department_1)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('321')
            ->setLastData('Sales Department_test,,,,600')
            ->setDstchannel('PJSIP/kamailio-0000026f')
            ->setDuration(6)
            ->setUniqueid($uniqID)
            ->setSrc('+10762027392')
            ->setTrueSrc('Local/320@queue_members-000000f5;2')
            ->setDepartment($department_1)
            ->setEmployee($employee_1)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('322')
            ->setLastData('PJSIP/321@kamailio,60,g')
            ->setDstchannel('PJSIP/kamailio-00000270')
            ->setDuration(19)
            ->setUniqueid($uniqID)
            ->setSrc('+10762027392')
            ->setDepartment($department_2)
            ->setEmployee($employee_2)
            ->create();

        $date_4 = $date->subSeconds(10);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('322')
            ->setLastData('PJSIP/322@kamailio,60,g')
            ->setDstchannel('PJSIP/kamailio-00000271')
            ->setDuration(18)
            ->setUniqueid($uniqID)
            ->setSrc('+10762027392')
            ->setDepartment($department_3)
            ->setEmployee($employee_3)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(3, $recs);
//dd($recs[0]);
        $this->assertEquals($recs[0]->from_num, '+10762027392');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '320');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->employee_id, $employee_1->id);
        $this->assertEquals($recs[0]->department_id, $department_1->id);

        $this->assertEquals($recs[1]->from_num, '+10762027392');
        $this->assertEquals($recs[1]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[1]->dialed, '321');
        $this->assertEquals($recs[1]->duration, $cdr_3->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_3->billsec);
        $this->assertEquals($recs[1]->employee_id, $employee_2->id);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        $this->assertEquals($recs[2]->from_num, '+10762027392');
        $this->assertEquals($recs[2]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[2]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[2]->dialed, '322');
        $this->assertEquals($recs[2]->duration, $cdr_4->duration);
        $this->assertEquals($recs[2]->billsec, $cdr_4->billsec);
        $this->assertEquals($recs[2]->employee_id, $employee_3->id);
        $this->assertEquals($recs[2]->department_id, $department_3->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 24.1
    /** @test */
    public function upload_cdr_crm_transfer_for_yourself_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+10758797573')
            ->setDst('1')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('321')
            ->setLastData('PJSIP/321@kamailio,60,g')
            ->setDstchannel('PJSIP/kamailio-0000223e')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('+10758797573')
            ->setTrueSrc('321')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setFromEmployee($employee_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, $cdr_2->src);
        $this->assertEquals($recs[0]->from_name, $cdr_2->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, '321');
        $this->assertEquals($recs[0]->dialed_name, $employee_2->getName());
        $this->assertEquals($recs[0]->duration, $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_2->billsec);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);
        $this->assertEquals($recs[0]->from_employee_id, $employee_2->id);

        $this->assertEquals($recs[1]->from_num, $cdr_2->src);
        $this->assertEquals($recs[1]->from_name, $cdr_2->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[1]->dialed, '321');
        $this->assertEquals($recs[1]->duration, $cdr_2->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_2->billsec);
        $this->assertEquals($recs[1]->employee_id, $employee->id);
        $this->assertEquals($recs[1]->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 24.2
    /** @test */
    public function upload_cdr_crm_transfer_for_yourself_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+10758797573')
            ->setDst('1')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(27)
            ->setSrc('+10758797573')
            ->setDst('1')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('321')
            ->setLastData('PJSIP/321@kamailio,60,g')
            ->setDstchannel('PJSIP/kamailio-0000223e')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('+10758797573')
            ->setTrueSrc('321')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_4 = $date->subSeconds(10);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('321')
            ->setLastData('PJSIP/321@kamailio,60,g')
            ->setDstchannel('')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('+10758797573')
            ->setTrueSrc('321')
            ->setTrueReasonHangup('CHANUNAVAIL')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, $cdr_3->src);
        $this->assertEquals($recs[0]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, '321');
        $this->assertEquals($recs[0]->dialed_name, $employee->getName());
        $this->assertEquals($recs[0]->duration, $cdr_3->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_3->billsec);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, $cdr_3->src);
        $this->assertEquals($recs[1]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '321');
        $this->assertEquals($recs[1]->duration, $cdr_3->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_3->billsec);
        $this->assertEquals($recs[1]->employee_id, $employee->id);
        $this->assertEquals($recs[1]->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 24.3
    /** @test */
    public function upload_cdr_crm_transfer_for_yourself_busy(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+10758797573')
            ->setDst('1')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('BUSY')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('PJSIP/321@kamailio,60,g')
            ->setDuration(27)
            ->setSrc('+10758797573')
            ->setTrueSrc('321')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setDst('321')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('321')
            ->setLastData('')
            ->setDstchannel('')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('+10758797573')
            ->setTrueSrc('321')
            ->setTrueReasonHangup('BUSY')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_4 = $date->subSeconds(10);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('321')
            ->setLastData('')
            ->setDstchannel('')
            ->setDuration(12)
            ->setUniqueid($uniqID)
            ->setSrc('+10758797573')
            ->setTrueSrc('321')
            ->setTrueReasonHangup('BUSY')
            ->setDepartment($department)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, $cdr_2->src);
        $this->assertEquals($recs[0]->from_name, $cdr_2->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, '321');
        $this->assertEquals($recs[0]->duration, $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_2->billsec);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, $cdr_2->src);
        $this->assertEquals($recs[1]->from_name, $cdr_2->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::BUSY());
        $this->assertEquals($recs[1]->dialed, '321');
        $this->assertEquals($recs[1]->duration, $cdr_2->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_2->billsec);
        $this->assertEquals($recs[1]->employee_id, $employee->id);
        $this->assertEquals($recs[1]->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 24.4
    /** @test */
    public function upload_cdr_crm_transfer_for_yourself_no_answer_some_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->setData(['number' => '333'])->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+10758797573')
            ->setDst('1')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(30);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+10758797573')
            ->setDst('1')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(25);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/320@queue_members-000000ee;1')
            ->setLastData('PJSIP/321@kamailio,60,g')
            ->setDuration(27)
            ->setSrc('+10758797573')
            ->setTrueSrc('321')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setDst('321')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, $cdr_3->src);
        $this->assertEquals($recs[0]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, '321');
        $this->assertEquals($recs[0]->duration, $cdr_3->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_3->billsec);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, $cdr_3->src);
        $this->assertEquals($recs[1]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '321');
        $this->assertEquals($recs[1]->duration, $cdr_3->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_3->billsec);
        $this->assertEquals($recs[1]->employee_id, $employee->id);
        $this->assertEquals($recs[1]->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_transfer_to_another_dept(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $employee Employee */
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000002c5;1')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00000f3c')
            ->setTrueSrc('Local/310@queue_members-000002c5;2')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('31036')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-000002c6;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setTrueReasonHangup('TRANSFER')
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-000002c6;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setTrueReasonHangup('TRANSFER')
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_5 = $date->subSeconds(15);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00007195')
            ->setTrueSrc('Local/311@queue_members-000002c6;2')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->setEmployee($employee_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '310');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[1]->dialed, '311');
        $this->assertEquals($recs[1]->duration, $cdr_3->duration + $cdr_5->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_3->billsec + $cdr_5->billsec);
        $this->assertEquals($recs[1]->serial_numbers, $cdr_3->serial);
        $this->assertEquals($recs[1]->case_id, $cdr_3->case_id);
        $this->assertEquals($recs[1]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[1]->employee_id, $employee_2->id);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_transfer_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $employee Employee */
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-000002d4;1')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009c95')
            ->setTrueSrc('Local/311@queue_members-000002d4;2')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('31036')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000002d5;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setTrueReasonHangup('TRANSFER')
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_4 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000002d5;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setTrueReasonHangup('NULL')
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_5 = $date->subSeconds(15);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000319;1')
            ->setTrueSrc(null)
            ->setTrueReasonHangup('NULL')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_6 = $date->subSeconds(10);
        $cdr_6 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_6)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000031a;1')
            ->setTrueSrc(null)
            ->setTrueReasonHangup('NULL')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_7 = $date->subSeconds(5);
        $cdr_7 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_7)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009d31')
            ->setTrueSrc('Local/310@queue_members-0000031a;2')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->setEmployee($employee_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '311');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[1]->dialed, '310');
        $this->assertEquals($recs[1]->duration, $cdr_6->duration + $cdr_7->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_6->billsec + $cdr_7->billsec);
        $this->assertEquals($recs[1]->serial_numbers, $cdr_7->serial);
        $this->assertEquals($recs[1]->case_id, $cdr_7->case_id);
        $this->assertEquals($recs[1]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[1]->employee_id, $employee_2->id);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_transfer_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $employee Employee */
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-000002d4;1')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009c95')
            ->setTrueSrc('Local/311@queue_members-000002d4;2')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('31036')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000002d5;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_4 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000002d5;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_5 = $date->subSeconds(15);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000319;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_6 = $date->subSeconds(10);
        $cdr_6 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_6)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000031a;1')
            ->setTrueSrc('Local/310@queue_members-0000031r;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->setEmployee($employee_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '311');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_6->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '310');
        $this->assertEquals($recs[1]->duration, $cdr_6->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_6->billsec);
        $this->assertEquals($recs[1]->serial_numbers, $cdr_6->serial);
        $this->assertEquals($recs[1]->case_id, $cdr_6->case_id);
        $this->assertEquals($recs[1]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[1]->employee_id, $employee_2->id);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_transfer_no_dstchannel(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $employee Employee */
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000457;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009c95')
            ->setTrueSrc('Local/310@queue_members-00000457;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('31036')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '310');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_4->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '');
        $this->assertEquals($recs[1]->duration, $cdr_4->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_4->billsec);
        $this->assertEquals($recs[1]->serial_numbers, $cdr_4->serial);
        $this->assertEquals($recs[1]->case_id, $cdr_4->case_id);
        $this->assertEquals($recs[1]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_transfer_has_dstchannel(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $employee Employee */
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000457;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009c95')
            ->setTrueSrc('Local/310@queue_members-00000457;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('31036')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000457;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_4 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000457;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '310');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_4->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '311');
        $this->assertEquals($recs[1]->duration, $cdr_4->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_4->billsec);
        $this->assertEquals($recs[1]->serial_numbers, $cdr_4->serial);
        $this->assertEquals($recs[1]->case_id, $cdr_4->case_id);
        $this->assertEquals($recs[1]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_some_transfer_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $employee Employee */
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000033b;1')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009d68')
            ->setTrueSrc('Local/310@queue_members-0000033b;2')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('31036')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-0000033c;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-0000033c;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_5 = $date->subSeconds(15);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009d69')
            ->setTrueSrc('Local/311@queue_members-0000033c;2')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setEmployee($employee_2)
            ->setDepartment($department_2)
            ->create();

        $date_6 = $date->subSeconds(10);
        $cdr_6 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_6)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000033d;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(38)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_7 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_6)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-0000033d;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(38)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_8 = $date->subSeconds(9);
        $cdr_8 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_8)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009d6a')
            ->setTrueSrc('Local/310@queue_members-0000033d;2')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->setEmployee($employee)
            ->create();

        $date_9 = $date->subSeconds(8);
        $cdr_9 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_9)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-0000033e;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(189)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $cdr_10 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_9)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-0000033e;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(189)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_11 = $date->subSeconds(5);
        $cdr_11 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_11)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009d6b')
            ->setTrueSrc('Local/311@queue_members-0000033e;2')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(10)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->setEmployee($employee_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(4, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '310');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[1]->dialed, '311');
        $this->assertEquals($recs[1]->duration, $cdr_3->duration + $cdr_5->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_3->billsec + $cdr_5->billsec);
        $this->assertEquals($recs[1]->serial_numbers, $cdr_5->serial);
        $this->assertEquals($recs[1]->case_id, $cdr_5->case_id);
        $this->assertEquals($recs[1]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[1]->employee_id, $employee_2->id);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        $this->assertEquals($recs[2]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[2]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[2]->dialed, '310');
        $this->assertEquals($recs[2]->duration, $cdr_8->duration + $cdr_7->duration);
        $this->assertEquals($recs[2]->billsec, $cdr_8->billsec + $cdr_7->billsec);
        $this->assertEquals($recs[2]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[2]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[2]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[2]->employee_id, $employee->id);
        $this->assertEquals($recs[2]->department_id, $department_2->id);

        $this->assertEquals($recs[3]->from_num, '+107794534');
        $this->assertEquals($recs[3]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[3]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[3]->dialed, '311');
        $this->assertEquals($recs[3]->duration, $cdr_10->duration + $cdr_11->duration);
        $this->assertEquals($recs[3]->billsec, $cdr_10->billsec + $cdr_11->billsec);
        $this->assertEquals($recs[3]->serial_numbers, $cdr_5->serial);
        $this->assertEquals($recs[3]->case_id, $cdr_5->case_id);
        $this->assertEquals($recs[3]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[3]->employee_id, $employee_2->id);
        $this->assertEquals($recs[3]->department_id, $department_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10.5
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_transfer_answer_one_recs(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000020;1')
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009d68')
            ->setTrueSrc('Local/311@queue_members-00000020;1')
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('31036')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00000068')
            ->setTrueSrc('Local/311@queue_members-00000020;2')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[0]->dialed, '311');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_3->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_3->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 10
    /** @test */
    public function upload_cdr_client_connect_to_queue_and_only_transfer_not_true_src(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000020;1')
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/324@queue_members-0000040c;1')
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-0000040d;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_4 = $date->subSeconds(10);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-0000040d;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_5 = $date->subSeconds(9);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-0000040d;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(18)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_6 = $date->subSeconds(8);
        $cdr_6 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_6)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/324@queue_members-0000040f;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_7 = $date->subSeconds(7);
        $cdr_7 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_7)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/320@queue_members-00000410;1')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(15)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(0, $recs);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // ??
    public function upload_cdr_client_connect_to_queue_and_transfer_answer_var_2(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $employee Employee */
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-0000040a;1')
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-0000040a;1')
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-0000a0a6')
            ->setTrueSrc(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(8)
            ->setTrueReasonHangup(null)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setEmployee($employee)
            ->setDepartment($department_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();
        $this->assertCount(4, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[0]->dialed, '310');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[0]->employee_id, $employee->id);
        $this->assertEquals($recs[0]->department_id, $department->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[1]->dialed, '311');
        $this->assertEquals($recs[1]->duration, $cdr_3->duration + $cdr_5->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_3->billsec + $cdr_5->billsec);
        $this->assertEquals($recs[1]->serial_numbers, $cdr_5->serial);
        $this->assertEquals($recs[1]->case_id, $cdr_5->case_id);
        $this->assertEquals($recs[1]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[1]->employee_id, $employee_2->id);
        $this->assertEquals($recs[1]->department_id, $department_2->id);

        $this->assertEquals($recs[2]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[2]->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($recs[2]->dialed, '310');
        $this->assertEquals($recs[2]->duration, $cdr_8->duration + $cdr_7->duration);
        $this->assertEquals($recs[2]->billsec, $cdr_8->billsec + $cdr_7->billsec);
        $this->assertEquals($recs[2]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[2]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[2]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[2]->employee_id, $employee->id);
        $this->assertEquals($recs[2]->department_id, $department_2->id);

        $this->assertEquals($recs[3]->from_num, '+107794534');
        $this->assertEquals($recs[3]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[3]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[3]->dialed, '311');
        $this->assertEquals($recs[3]->duration, $cdr_10->duration + $cdr_11->duration);
        $this->assertEquals($recs[3]->billsec, $cdr_10->billsec + $cdr_11->billsec);
        $this->assertEquals($recs[3]->serial_numbers, $cdr_5->serial);
        $this->assertEquals($recs[3]->case_id, $cdr_5->case_id);
        $this->assertEquals($recs[3]->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($recs[3]->employee_id, $employee_2->id);
        $this->assertEquals($recs[3]->department_id, $department_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 12.1
    /** @test */
    public function upload_cdr_crm_transfer_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(29)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setChannel($channel)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-0000021f')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(7)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();


        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '+107794534');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($rec_1->dialed, 'admin');
        $this->assertEquals($rec_1->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($rec_1->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertEquals($rec_1->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_2->from_num, '+107794534');
        $this->assertEquals($rec_2->from_name, $cdr_1->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($rec_2->dialed, '312');
        $this->assertEquals($rec_2->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($rec_2->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($rec_2->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_2->case_id, $queue->case_id);
        $this->assertEquals($rec_2->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 12.2
    /** @test */
    public function upload_cdr_crm_transfer_queue_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000338;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(29)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000339;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(7)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009d63')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(4)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, 'admin');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec);
        $this->assertEquals($recs[0]->department_id, $department->id);
        $this->assertNull($recs[0]->employee_id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($recs[1]->dialed, '310');
        $this->assertEquals($recs[1]->duration, $cdr_2->duration + $cdr_3->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_1->billsec + $cdr_3->billsec);
        $this->assertEquals($recs[1]->department_id, $department->id);
        $this->assertNull($recs[1]->employee_id,);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 12.3
    /** @test */
    public function upload_cdr_crm_transfer_queue_all_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000338;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(29)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-00000339;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(7)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('NULL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-00000386;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(4)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, 'admin');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec);
        $this->assertEquals($recs[0]->department_id, $department->id);
        $this->assertNull($recs[0]->employee_id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '311');
        $this->assertEquals($recs[1]->duration, $cdr_2->duration + $cdr_3->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_1->billsec + $cdr_3->billsec);
        $this->assertEquals($recs[1]->department_id, $department->id);
        $this->assertNull($recs[1]->employee_id,);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 21.2 (12)
    /** @test */
    public function upload_cdr_crm_transfer_queue_no_answer_last(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000003e7;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(29)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000003e8;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(7)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(4)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, 'admin');
        $this->assertEquals($recs[0]->duration, $cdr_3->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_3->billsec);
        $this->assertEquals($recs[0]->department_id, $department->id);
        $this->assertEquals($recs[0]->employee_id, $employee->id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_3->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '');
        $this->assertEquals($recs[1]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[1]->department_id, $department->id);
//        $this->assertEquals($recs[1]->employee_id, $employee->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 21.1 (12)
    /** @test */
    public function upload_cdr_crm_transfer_ore_rec(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/310@queue_members-000003e7;1')
            ->setTrueSrc('admin')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(29)
            ->setSrc('+107794534')
            ->setDst('3')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel('PJSIP/kamailio-00009d60')
            ->setDepartment($department)
            ->create();


        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(2, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($recs[0]->dialed, 'admin');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec);
        $this->assertEquals($recs[0]->department_id, $department->id);
        $this->assertNull($recs[0]->employee_id);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($recs[1]->dialed, '');
        $this->assertEquals($recs[1]->duration, $cdr_1->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_1->billsec);
        $this->assertEquals($recs[1]->department_id, $department->id);
        $this->assertNull($recs[1]->employee_id,);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 14
    /** @test */
    public function upload_cdr_crm_transfer_agent_answered(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('310')
            ->setLastData('PJSIP/310@kamailio,60,g')
            ->setDuration(117)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setChannel($channel)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDstchannel('PJSIP/kamailio-0000021f')
            ->setTrueSrc('310')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(0)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $histories = History::get();

        $rec_1 = $histories[0];

        $this->assertEquals($rec_1->from_num, '314');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = $histories[1];

        $this->assertEquals($rec_2->from_num, '314');
        $this->assertEquals($rec_2->from_name, $cdr_1->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($rec_2->dialed, '310');
        $this->assertEquals($rec_2->duration, $cdr_1->duration);
        $this->assertEquals($rec_2->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_2->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_2->case_id, $queue->case_id);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 14
    /** @test */
    public function upload_cdr_crm_transfer_agent_busy(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('BUSY')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('310')
            ->setLastData('PJSIP/310@kamailio,60,g')
            ->setDuration(117)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('BUSY')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setChannel($channel)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDstchannel('PJSIP/kamailio-0000021f')
            ->setTrueSrc('310')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(0)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $histories = History::get();

        $rec_1 = $histories[0];

        $this->assertEquals($rec_1->from_num, '314');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = $histories[1];

        $this->assertEquals($rec_2->from_num, '314');
        $this->assertEquals($rec_2->from_name, $cdr_1->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::BUSY);
        $this->assertEquals($rec_2->dialed, '310');
        $this->assertEquals($rec_2->duration, $cdr_1->duration);
        $this->assertEquals($rec_2->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_2->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_2->case_id, $queue->case_id);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 14
    /** @test */
    public function upload_cdr_crm_transfer_agent_chanunaval(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('310')
            ->setLastData('PJSIP/310@kamailio,60,g')
            ->setDuration(117)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CHANUNAVAIL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setChannel($channel)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDstchannel('PJSIP/kamailio-0000021f')
            ->setTrueSrc('310')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(0)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $histories = History::get();

        $rec_1 = $histories[0];

        $this->assertEquals($rec_1->from_num, '314');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($rec_1->dialed, '310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = $histories[1];

        $this->assertEquals($rec_2->from_num, '314');
        $this->assertEquals($rec_2->from_name, $cdr_1->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::NO_ANSWER);
        $this->assertEquals($rec_2->dialed, '310');
        $this->assertEquals($rec_2->duration, $cdr_1->duration);
        $this->assertEquals($rec_2->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_2->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_2->case_id, $queue->case_id);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 14.1 without hangup
    /** @test */
    public function upload_cdr_crm_transfer_agent_without_hangup(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('+4554310')
            ->setLastData('PJSIP/310@kamailio,60,g')
            ->setDuration(117)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $histories = History::get();

        $rec_1 = $histories[0];

        $this->assertEquals($rec_1->from_num, '314');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($rec_1->dialed, '+4554310');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = $histories[1];

        $this->assertEquals($rec_2->from_num, '314');
        $this->assertEquals($rec_2->from_name, $cdr_1->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::NO_ANSWER);
        $this->assertEquals($rec_2->dialed, '310');
        $this->assertEquals($rec_2->duration, $cdr_1->duration);
        $this->assertEquals($rec_2->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_2->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_2->case_id, $queue->case_id);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 14
    /** @test */
    public function upload_cdr_crm_transfer_agent_answered_some_dial(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        $sip_3 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();
        $employee_3 = $this->employeeBuilder->setSip($sip_3)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('admin')
            ->setLastData('PJSIP/310@kamailio,60,g')
            ->setDuration(117)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('310')
            ->setLastData('PJSIP/311@kamailio,60,g')
            ->setDuration(17)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee_2)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDstchannel('Local/312@queue_members-00000001;1')
            ->setTrueSrc('310')
            ->setLastData('PJSIP/313@kamailio,60,g')
            ->setDuration(137)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setChannel($channel)
            ->setDepartment($department)
            ->setEmployee($employee_3)
            ->create();


        $date_4 = $date->subSeconds(15);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_4)
            ->setChannel($channel)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDstchannel('PJSIP/kamailio-0000021f')
            ->setTrueSrc('310')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(0)
            ->setSrc('314')
            ->setDst('310')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(4, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '314');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CRM_TRANSFER());
        $this->assertEquals($rec_1->dialed, 'admin');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertNull($rec_1->employee_id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->get()[1]
        ;

        $this->assertEquals($rec_2->from_num, '314');
        $this->assertEquals($rec_2->from_name, $cdr_1->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($rec_2->dialed, '310');
        $this->assertEquals($rec_2->duration, $cdr_1->duration);
        $this->assertEquals($rec_2->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_2->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_2->case_id, $queue->case_id);
        $this->assertEquals($rec_2->employee_id, $employee->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        $rec_3 = History::query()
            ->where('call_date', $date_2)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_3->from_num, '314');
        $this->assertEquals($rec_3->from_name, $cdr_2->clid);
        $this->assertEquals($rec_3->status->value, HistoryStatus::TRANSFER());
        $this->assertEquals($rec_3->dialed, '311');
        $this->assertEquals($rec_3->duration, $cdr_2->duration);
        $this->assertEquals($rec_3->billsec, $cdr_2->billsec);
        $this->assertEquals($rec_3->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_3->case_id, $queue->case_id);
        $this->assertEquals($rec_3->employee_id, $employee_2->id);
        $this->assertEquals($rec_3->department_id, $department->id);

        $rec_4 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_4->from_num, '314');
        $this->assertEquals($rec_4->from_name, $cdr_3->clid);
        $this->assertEquals($rec_4->status->value, HistoryStatus::ANSWERED());
        $this->assertEquals($rec_4->dialed, '313');
        $this->assertEquals($rec_4->duration, $cdr_3->duration);
        $this->assertEquals($rec_4->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_4->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_4->case_id, $queue->case_id);
        $this->assertEquals($rec_4->employee_id, $employee_3->id);
        $this->assertEquals($rec_4->department_id, $department->id);

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 13
    /** @test */
    public function upload_cdr_queue_timeout(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDstchannel('Local/310@queue_members-0000021d;1')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(619)
            ->setChannel($channel)
            ->setSrc('+107577953')
            ->setDst('1')
            ->setDepartment($department)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '+107577953');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($rec_1->dialed, '1');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $queue->serial_number);
        $this->assertEquals($rec_1->case_id, $queue->case_id);
        $this->assertEquals($rec_1->comment, $queue->comment);
        $this->assertEquals($rec_1->lastapp, CdrEntity::TYPE_HANGUP);
        $this->assertNull($rec_1->employee_id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 11
    /** @test */
    public function upload_cdr_client_connect_to_queue_but_no_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $channel = 'PJSIP/kamailio-00001405';
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setChannel($channel)
            ->setStatus(QueueStatus::WAIT())
            ->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setChannel($channel)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('Local/311@queue_members-0000004c;1')
            ->setTrueReasonHangup('NO ANSWER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $queue->refresh();
        $this->assertTrue($queue->status->isCancel());

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '+107794534');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::NO_ANSWER());
        $this->assertEquals($rec_1->dialed, '311');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->lastapp, CdrEntity::TYPE_QUEUE);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 19
    /** @test */
    public function upload_cdr_client_queue_has_transfer_and_crm_transfer_answer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel("Local/{$employee->sip->number}@queue_members-000003b2;1")
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(17)
            ->setSrc('+107794534')
            ->setTrueSrc('admin')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009ecf')
            ->setTrueReasonHangup('NULL')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(7)
            ->setSrc('+107794534')
            ->setTrueSrc('admin')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel("Local/{$employee_2->sip->number}@queue_members-000003b3;1")
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(27)
            ->setSrc('+107794534')
            ->setTrueSrc('admin')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel("Local/{$employee_2->sip->number}@queue_members-000003b3;1")
            ->setTrueReasonHangup('TRANSFER')
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(27)
            ->setSrc('+107794534')
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->create();

        $date_5 = $date->subSeconds(10);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDstchannel('PJSIP/kamailio-00009ed0')
            ->setTrueReasonHangup(null)
            ->setLastData('Sales Department_test,,,,600')
            ->setDuration(277)
            ->setSrc('+107794534')
            ->setTrueSrc(null)
            ->setDst('1')
            ->setClid('"Anders 6N" <+107794534>')
            ->setDepartment($department_2)
            ->setEmployee($employee_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(3, $recs);

        $this->assertEquals($recs[0]->from_num, '+107794534');
        $this->assertEquals($recs[0]->dialed, 'admin');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::CRM_TRANSFER);
        $this->assertEquals($recs[0]->department_id, $department->id);
        $this->assertNull($recs[0]->employee_id);
        $this->assertNull($recs[0]->dialed_name);

        $this->assertEquals($recs[1]->from_num, '+107794534');
        $this->assertEquals($recs[1]->dialed, $employee->sip->number);
        $this->assertEquals($recs[1]->dialed_name, $employee->getName());
        $this->assertEquals($recs[1]->duration, $cdr_1->duration + $cdr_2->duration);
        $this->assertEquals($recs[1]->billsec, $cdr_1->billsec + $cdr_2->billsec);
        $this->assertEquals($recs[1]->status->value, HistoryStatus::TRANSFER);
        $this->assertEquals($recs[1]->department_id, $department->id);
        $this->assertEquals($recs[1]->employee_id, $employee->id);

        $this->assertEquals($recs[2]->from_num, '+107794534');
        $this->assertEquals($recs[2]->dialed, $employee_2->sip->number);
        $this->assertEquals($recs[2]->dialed_name, $employee_2->getName());
        $this->assertEquals($recs[2]->duration, $cdr_4->duration + $cdr_5->duration);
        $this->assertEquals($recs[2]->billsec, $cdr_4->billsec + $cdr_5->billsec);
        $this->assertEquals($recs[2]->status->value, HistoryStatus::ANSWERED);
        $this->assertEquals($recs[2]->department_id, $department_2->id);
        $this->assertEquals($recs[2]->employee_id, $employee_2->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    /** @test */
    public function upload_cdr_dial_between_agents(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $uniqID_2 = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('2002')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('2002')
            ->setLastData('PJSIP/2000@kamailio,60,g')
            ->setDuration(0)
            ->setUniqueid($uniqID)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup(CdrEntity::TYPE_DIAL)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('2005')
            ->setLastData('')
            ->setDuration(10)
            ->setUniqueid($uniqID_2)
            ->setSrc($sip_2->number)
            ->setDepartment($department)
            ->setEmployee($employee_2)
            ->setSerialNumber('45465465465')
            ->setCaseId('8989')
            ->create();

        $date_4 = $date->subSeconds(15);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup(CdrEntity::TYPE_DIAL)
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('2005')
            ->setLastData('')
            ->setDuration(0)
            ->setUniqueid($uniqID_2)
            ->setSrc($sip_2->number)
            ->setDepartment($department)
            ->setEmployee($employee_2)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(2, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::ANSWERED);
        $this->assertEquals($rec_1->dialed, '2002');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        $rec_2 = History::query()
            ->where('call_date', $date_3)
            ->where('uniqueid', $uniqID_2)
            ->first()
        ;
        $this->assertEquals($rec_2->from_num, $cdr_3->src);
        $this->assertEquals($rec_2->from_name, $cdr_3->clid);
        $this->assertEquals($rec_2->status->value, HistoryStatus::ANSWERED);
        $this->assertEquals($rec_2->dialed, '2005');
        $this->assertEquals($rec_2->duration, $cdr_3->duration);
        $this->assertEquals($rec_2->billsec, $cdr_3->billsec);
        $this->assertEquals($rec_2->serial_numbers, $cdr_3->serial);
        $this->assertEquals($rec_2->case_id, $cdr_3->case_id);
        $this->assertEquals($rec_2->lastapp, $cdr_3->lastapp);
        $this->assertEquals($rec_2->employee_id, $employee_2->id);
        $this->assertEquals($rec_2->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 17
    /** @test */
    public function upload_cdr_dial_cancel_no_dst(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CANCEL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('s')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, $cdr_1->src);
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CANCEL);
        $this->assertEquals($rec_1->dialed, '');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertEquals($rec_1->employee_id, $employee->id);
        $this->assertEquals($rec_1->department_id, $department->id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 17
    /** @test */
    public function upload_cdr_background_cancel_no_dst(): void
    {
        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CANCEL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_BACKGROUND)
            ->setDst('s')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('304')
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '304');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CANCEL);
        $this->assertEquals($rec_1->dialed, '');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 20
    /** @test */
    public function upload_cdr_playback_no_answer(): void
    {
        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_PLAYBACK)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('+107900070')
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '+107900070');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::NO_ANSWER);
        $this->assertEquals($rec_1->dialed, '555');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 20
    /** @test */
    public function upload_cdr_only_hangup_no_answer(): void
    {
        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('+107900070')
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '+107900070');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::NO_ANSWER);
        $this->assertEquals($rec_1->dialed, '555');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 5
    /** @test */
    public function upload_cdr_ring_client_to_ring_no_answer(): void
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('321')
            ->setLastData('PJSIP/321@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('+10773048632')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CHANUNAVAIL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('321')
            ->setLastData(' ')
            ->setDuration(7)
            ->setSrc('+10773048632')
            ->setDepartment($department)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->from_num, '+10773048632');
        $this->assertEquals($recs[0]->from_name, $cdr_1->clid);
        $this->assertEquals($recs[0]->status->value, HistoryStatus::NO_ANSWER);
        $this->assertEquals($recs[0]->dialed, '321');
        $this->assertEquals($recs[0]->duration, $cdr_1->duration);
        $this->assertEquals($recs[0]->billsec, $cdr_1->billsec);
        $this->assertEquals($recs[0]->serial_numbers, $cdr_1->serial);
        $this->assertEquals($recs[0]->case_id, $cdr_1->case_id);
        $this->assertEquals($recs[0]->lastapp, $cdr_1->lastapp);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    // case 23

    public function upload_cdr_only_queue_dial_transfer_crm_transfer(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();
        $sip_3 = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();
        $department_3 = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)
            ->setDepartment($department_2)->create();
        $employee_3 = $this->employeeBuilder->setSip($sip_3)
            ->setDepartment($department_3)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup(null)
            ->setDstchannel('Local/310@queue_members-00000481;1')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(19)
            ->setSrc('+107900070')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(25);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('NO ANSWER')
            ->setTrueReasonHangup(null)
            ->setDstchannel('Local/310@queue_members-00000481;1')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(10)
            ->setSrc('+107900070')
            ->setDepartment($department)
            ->create();

        $date_3 = $date->subSeconds(20);
        $cdr_3 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CRM_TRANSFER')
            ->setDstchannel('Local/311@queue_members-00000483;1')
            ->setTrueSrc('admin')
            ->setUniqueid($uniqID)
            ->setCallDate($date_3)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(13)
            ->setSrc('+107900070')
            ->setDepartment($department_2)
            ->create();

        $date_4 = $date->subSeconds(15);
        $cdr_4 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NULL')
            ->setDstchannel('PJSIP/kamailio-0000a1fa')
            ->setTrueSrc('admin')
            ->setUniqueid($uniqID)
            ->setCallDate($date_4)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(16)
            ->setSrc('+107900070')
            ->setDepartment($department_2)
            ->setEmployee($employee)
            ->create();

        $date_5 = $date->subSeconds(10);
        $cdr_5 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setDstchannel('Local/312@queue_members-00000484;1')
            ->setTrueSrc('admin')
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(10)
            ->setSrc('+107900070')
            ->setDepartment($department_3)
            ->create();

        $cdr_6 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setDstchannel('Local/312@queue_members-00000484;1')
            ->setTrueSrc(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_5)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('555')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(10)
            ->setSrc('+107900070')
            ->setDepartment($department_3)
            ->create();

        $date_7 = $date->subSeconds(9);
        $cdr_7 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('NULL')
            ->setDstchannel('PJSIP/kamailio-0000a1fb')
            ->setTrueSrc('PJSIP/kamailio-0000a1fa')
            ->setUniqueid($uniqID)
            ->setCallDate($date_7)
            ->setLastapp(CdrEntity::TYPE_QUEUE)
            ->setDst('310')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(18)
            ->setSrc('+107900070')
            ->setDepartment($department_3)
            ->setEmployee($employee_2)
            ->create();

        $date_8 = $date->subSeconds(8);
        $cdr_8 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setDstchannel('PJSIP/kamailio-0000a1fc')
            ->setTrueSrc(null)
            ->setUniqueid($uniqID)
            ->setCallDate($date_8)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('310')
            ->setLastData('PJSIP/310@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('+107900070')
            ->setDepartment($department)
            ->setEmployee($employee_3)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $recs = History::all();

        $this->assertCount(4, $recs);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    /** @test */
    public function upload_cdr_background_cancel_has_dst(): void
    {
        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('CANCEL')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_BACKGROUND)
            ->setDst('409')
            ->setLastData('PJSIP/3000@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('304')
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '304');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CANCEL);
        $this->assertEquals($rec_1->dialed, '409');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertNull($rec_1->employee_id);
        $this->assertNull($rec_1->department_id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    public function upload_cdr_one_entry(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $uniqID = $this->faker->uuid;
        $date = CarbonImmutable::now();

        $date_1 = $date->subSeconds(30);
        $cdr_1 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('TRANSFER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_1)
            ->setLastapp(CdrEntity::TYPE_DIAL)
            ->setDst('311')
            ->setDstchannel('PJSIP/kamailio-00009cee')
            ->setLastData('PJSIP/311@kamailio,60,g')
            ->setDuration(7)
            ->setSrc('444231')
            ->setDepartment($department)
            ->create();

        $date_2 = $date->subSeconds(20);
        $cdr_2 = $this->cdrBuilder
            ->setDisposition('ANSWERED')
            ->setTrueReasonHangup('ANSWER')
            ->setUniqueid($uniqID)
            ->setCallDate($date_2)
            ->setLastapp(CdrEntity::TYPE_HANGUP)
            ->setDst('311')
            ->setDstchannel('PJSIP/kamailio-00009cee')
            ->setLastData('PJSIP/555666@kamailio,,b(custom-privacy-header^s^1)')
            ->setDuration(7)
            ->setSrc('444231')
            ->setDepartment($department)
            ->create();

        $this->assertEquals(0, History::query()->count());

        $this->cdrService->uploadCdrData();

        $this->assertEquals(1, History::query()->count());

        $rec_1 = History::query()
            ->where('call_date', $date_1)
            ->where('uniqueid', $uniqID)
            ->first()
        ;

        $this->assertEquals($rec_1->from_num, '304');
        $this->assertEquals($rec_1->from_name, $cdr_1->clid);
        $this->assertEquals($rec_1->status->value, HistoryStatus::CANCEL);
        $this->assertEquals($rec_1->dialed, '409');
        $this->assertEquals($rec_1->duration, $cdr_1->duration);
        $this->assertEquals($rec_1->billsec, $cdr_1->billsec);
        $this->assertEquals($rec_1->serial_numbers, $cdr_1->serial);
        $this->assertEquals($rec_1->case_id, $cdr_1->case_id);
        $this->assertEquals($rec_1->lastapp, $cdr_1->lastapp);
        $this->assertNull($rec_1->employee_id);
        $this->assertNull($rec_1->department_id);

        foreach ($this->cdrService->getAll() as $cdr){
            $this->assertEquals(1, $cdr->is_fetch);
        }
    }

    /** @test */
    public function not_upload_cdr_no_department(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $cdr = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setEmployee($employee)
            ->create();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
        $this->assertNull($cdr->is_fetch);

        $this->cdrService->uploadCdrData();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
    }

    /** @test */
    public function not_upload_cdr_wrong_lastapp(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $cdr = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setLastapp('Echo')
            ->create();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
        $this->assertNull($cdr->is_fetch);

        $this->cdrService->uploadCdrData();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
    }

    /** @test */
    public function not_upload_cdr_is_fetching(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();

        $cdr = $this->cdrBuilder
            ->setSrc($sip->number)
            ->setDepartment($department)
            ->setEmployee($employee)
            ->setIsFetching(1)
            ->create();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );

        $this->cdrService->uploadCdrData();

        $this->assertNull(
            History::query()
                ->where('call_date', $cdr->calldate)
                ->where('uniqueid', $cdr->uniqueid)
                ->first()
        );
    }

    /** @test */
    public function parse_number(): void
    {
        $value = 'PJSIP/3000@kamailio,60,g';

        $num = $this->cdrService::parseNumFromChanel($value);

        $this->assertEquals('3000', $num);
    }

    /** @test */
    public function parse_name(): void
    {
        $str = '"Victoria Sales" <310>';

        $name = $this->cdrService::parseNameFromClid($str);

        $this->assertEquals('Victoria Sales', $name);
    }

    /** @test */
    public function parse_name_empty(): void
    {
        $str = '"" <310>';

        $name = $this->cdrService::parseNameFromClid($str);

        $this->assertNull($name);
    }
}
