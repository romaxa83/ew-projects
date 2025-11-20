<?php

namespace Tests\Unit\Commands\Helpers\PAMI;

use App\Console\Commands\Helpers\PAMI\QueueEntryHandler;
use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\Models\Calls\Queue;
use App\PAMI\Message\Event;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;
use Tests\Traits\AmiEventHelper;

class QueueEntryHandlerTest extends TestCase
{
    use DatabaseTransactions;
    use AmiEventHelper;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected QueueBuilder $queueBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }


    /** @test */
    public function add_records(): void
    {
        $d_1 = $this->departmentBuilder->create();
        $d_2 = $this->departmentBuilder->create();

        $data = [
            0 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed4',
                'asterisk-docker01-1679902021.54387',
                3,
                15,
                '+12-54334-534-5',
                'call_name',
                '9999',
                'connected_name',
            ),
            1 => $this->createQueueEntryEvent(
                $d_2->name,
                'PJSIP/kamailio-00001ed5',
                'asterisk-docker01-1679902081.54394',
                4,
                5
            ),
            2 => $this->createQueueEntryEvent(
                'test',
                'PJSIP/kamailio-00001ed6',
                'asterisk-docker01-1679902081.54396',
                4,
                10
            ),
            3 => $this->createQueueMemberEvent(),
            4 => $this->createQueueMemberEvent()
        ];

        $sn_1 = '9999WER7868';
        $sn_event_1 = $this->createVarSetEvent(Event\VarSetEvent::SERIAL_NUMBER, $sn_1, $data[0]->getChannel());
        Cache::tags('queue_call')->put(
            Event\VarSetEvent::SERIAL_NUMBER.'_'. $data[0]->getChannel(), $sn_event_1
        );
        $case_1 = '9999';
        $case_event_1 = $this->createVarSetEvent(Event\VarSetEvent::CASE_ID, $case_1, $data[0]->getChannel());
        Cache::tags('queue_call')->put(
            Event\VarSetEvent::CASE_ID.'_'. $data[0]->getChannel(), $case_event_1
        );

        $this->assertNull(Queue::query()->where('uniqueid', $data[0]->getUniqueid())->first());
        $this->assertNull(Queue::query()->where('uniqueid', $data[1]->getUniqueid())->first());
        $this->assertNull(Queue::query()->where('uniqueid', $data[2]->getUniqueid())->first());

        /** @var $handler QueueEntryHandler */
        $handler = resolve(QueueEntryHandler::class);
        $handler->handler($data);

        $rec_1 = Queue::query()->where('uniqueid', $data[0]->getUniqueid())->first();

        $this->assertEquals($rec_1->department_id, $d_1->id);
        $this->assertEquals($rec_1->caller_num, $data[0]->getCallerIDNum());
        $this->assertEquals($rec_1->caller_name, $data[0]->getCallerIDName());
        $this->assertEquals($rec_1->connected_num, $data[0]->getConnectedLineNum());
        $this->assertEquals($rec_1->connected_name, $data[0]->getConnectedLineName());
        $this->assertEquals($rec_1->position, $data[0]->getPosition());
        $this->assertEquals($rec_1->wait, $data[0]->getWait());
        $this->assertEquals($rec_1->channel, $data[0]->getChannel());
        $this->assertTrue($rec_1->status->isWait());
        $this->assertEquals($rec_1->serial_number, $sn_1);
        $this->assertEquals($rec_1->case_id, $case_1);
        $this->assertNull($rec_1->employee_id);
        $this->assertNull($rec_1->connected_at);
        $this->assertEquals($rec_1->type->value, QueueType::QUEUE);

        $rec_2 = Queue::query()->where('uniqueid', $data[1]->getUniqueid())->first();

        $this->assertEquals($rec_2->department_id, $d_2->id);
        $this->assertEquals($rec_2->caller_num, $data[1]->getCallerIDNum());
        $this->assertEquals($rec_2->caller_name, $data[1]->getCallerIDName());
        $this->assertEquals($rec_2->connected_num, $data[1]->getConnectedLineNum());
        $this->assertEquals($rec_2->connected_name, $data[1]->getConnectedLineName());
        $this->assertEquals($rec_2->position, $data[1]->getPosition());
        $this->assertEquals($rec_2->wait, $data[1]->getWait());
        $this->assertEquals($rec_2->channel, $data[1]->getChannel());
        $this->assertTrue($rec_1->status->isWait());
        $this->assertNull($rec_2->employee_id);
        $this->assertNull($rec_2->serial_number);
        $this->assertNull($rec_2->case_id);
        $this->assertNull($rec_2->connected_at);
        $this->assertEquals($rec_2->type->value, QueueType::QUEUE);

        $this->assertNull(Queue::query()->where('uniqueid', $data[2]->getUniqueid())->first());

        Cache::tags('queue_call')->delete(Event\VarSetEvent::SERIAL_NUMBER.'_'. $data[0]->getChannel());
        Cache::tags('queue_call')->delete(Event\VarSetEvent::CASE_ID.'_'. $data[0]->getChannel());
    }

    /** @test */
    public function update_and_create_records(): void
    {
        $position = 3;
        $newPosition = 2;
        $wait = 30;
        $newWait = 35;
        $q_1_uniqueid = 'asterisk-docker01-1679902021.54387';
        $q_1 = $this->queueBuilder->setStatus(QueueStatus::WAIT())
            ->setPosition($position)
            ->setWait($wait)
            ->setUniqueid($q_1_uniqueid)->create();

        $d_1 = $this->departmentBuilder->create();

        $data = [
            0 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed6',
                'asterisk-docker01-1679902021.54389',
                3,
                15,
                '+12-54334-534-5',
                'call_name',
                '9999',
                'connected_name',
            ),
            1 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed7',
                $q_1_uniqueid,
                $newPosition,
                $newWait,
                '+12-54334-534-5',
                'call_name',
                '9999',
                'connected_name',
            ),
        ];

        $this->assertNotEquals($q_1->position, $newPosition);
        $this->assertNotEquals($q_1->wait, $newWait);

        $this->assertEquals(1, Queue::count());

        /** @var $handler QueueEntryHandler */
        $handler = resolve(QueueEntryHandler::class);
        $handler->handler($data);

        $this->assertEquals(2, Queue::count());

        $q_1->refresh();

        $this->assertEquals($q_1->position, $newPosition);
        $this->assertEquals($q_1->wait, $newWait);
    }

    /** @test */
    public function some_records_connection(): void
    {
        $sip_1 = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();

        $q_1_uniqueid = 'asterisk-docker01-1679902021.54387';
        /**@var $q_1 Queue */
        $q_1 = $this->queueBuilder->setStatus(QueueStatus::WAIT())
            ->setUniqueid($q_1_uniqueid)->create();

        $d_1 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($d_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($d_1)->create();

        $data = [
            0 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed6',
                'asterisk-docker01-1679902021.54389',
                3,
                15,
                '+12-54334-534-5',
                'call_name',
                '999',
            ),
            1 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed7',
                $q_1_uniqueid,
                ConnectedLineNum: $sip_2->number
            ),
            $this->createQueueMemberEvent(
                location: 'Local/'.$sip_2->number.'@queue_members',
                status: Event\QueueMemberEvent::STATUS_AST_DEVICE_RINGING
            ),
        ];

        $this->assertTrue($q_1->status->isWait());
        $this->assertNull($q_1->connected_at);
        $this->assertNull($q_1->called_at);
        $this->assertNull($q_1->employee_id);

        /** @var $handler QueueEntryHandler */
        $handler = resolve(QueueEntryHandler::class);
        $handler->handler($data);

        $q_1->refresh();

        $this->assertTrue($q_1->status->isConnection());
//        $this->assertNotNull($q_1->connected_at);
        $this->assertNull($q_1->called_at);
        $this->assertEquals($q_1->employee_id, $employee_2->id);
    }

    /** @test */
    public function some_record_un_connection_and_un_called(): void
    {
        $sip_1 = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();

        $q_1_uniqueid = 'asterisk-docker01-1679902021.54387';
        /**@var $q_1 Queue */
        $q_1 = $this->queueBuilder
            ->setStatus(QueueStatus::CONNECTION())
            ->setConnectedAt(CarbonImmutable::now())
            ->setCalledAt(CarbonImmutable::now())
            ->setInCall(1)
            ->setUniqueid($q_1_uniqueid)->create();

        $d_1 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($d_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($d_1)->create();

        $data = [
            0 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed6',
                'asterisk-docker01-1679902021.54389',
                3,
                15,
                '+12-54334-534-5',
                'call_name',
                '999',
            ),
            1 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed7',
                $q_1_uniqueid,
                ConnectedLineNum: $sip_2->number
            ),
            $this->createQueueMemberEvent(
                location: 'Local/'.$sip_2->number.'@queue_members',
                status: Event\QueueMemberEvent::STATUS_AST_DEVICE_NOT_INUSE,
                inCall: 0
            ),
        ];

        $this->assertNotNull($q_1->connected_at);
        $this->assertNotNull($q_1->called_at);

        /** @var $handler QueueEntryHandler */
        $handler = resolve(QueueEntryHandler::class);
        $handler->handler($data);

        $q_1->refresh();

        $this->assertNull($q_1->connected_at);
        $this->assertNull($q_1->called_at);
    }

    /** @test */
    public function some_record_not_un_connection_and_un_called(): void
    {
        $sip_1 = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();

        $q_1_uniqueid = 'asterisk-docker01-1679902021.54387';
        /**@var $q_1 Queue */
        $q_1 = $this->queueBuilder
            ->setStatus(QueueStatus::CONNECTION())
            ->setConnectedAt(CarbonImmutable::now())
            ->setCalledAt(CarbonImmutable::now())
            ->setInCall(1)
            ->setUniqueid($q_1_uniqueid)->create();

        $d_1 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($d_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($d_1)->create();

        $data = [
            0 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed6',
                'asterisk-docker01-1679902021.54389',
                3,
                15,
                '+12-54334-534-5',
                'call_name',
                '999',
            ),
            1 => $this->createQueueEntryEvent(
                $d_1->name,
                'PJSIP/kamailio-00001ed7',
                $q_1_uniqueid,
                ConnectedLineNum: $sip_2->number
            ),
            $this->createQueueMemberEvent(
                location: 'Local/'.$sip_2->number.'@queue_members',
                status: Event\QueueMemberEvent::STATUS_AST_DEVICE_NOT_INUSE,
                inCall: 1
            ),
        ];

        $this->assertNotNull($q_1->connected_at);
        $this->assertNotNull($q_1->called_at);

        /** @var $handler QueueEntryHandler */
        $handler = resolve(QueueEntryHandler::class);
        $handler->handler($data);

        $q_1->refresh();

//        $this->assertNotNull($q_1->connected_at);
//        $this->assertNotNull($q_1->called_at);
    }
}
