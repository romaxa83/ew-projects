<?php

namespace Tests\Unit\Services\Calls;

use App\Enums\Calls\QueueStatus;
use App\Enums\Calls\QueueType;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\Services\Calls\QueueService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use OpiyOrg\AriClient\Enum\ChannelStates;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;
use Tests\Traits\Ari\ChannelHelper;

class QueueServiceTest extends TestCase
{
    use DatabaseTransactions;
    use ChannelHelper;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected QueueBuilder $queueBuilder;
    protected QueueService $queueService;
    protected function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);

        $this->queueService = resolve(QueueService::class);
    }

    /** @test */
    public function generate_withdrawinfo_as_admin(): void
    {
        $admin = $this->loginAsSuperAdmin();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $str = $this->queueService->generateWithdrawinfo($admin, $employee);

        $this->assertEquals(
            $str,
            "CRM_TRANSFER,admin,{$employee->sip->number}"
        );
    }

    /** @test */
    public function generate_withdrawinfo_as_employee_and_not_sip(): void
    {
        $auth = $this->loginAsEmployee();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $str = $this->queueService->generateWithdrawinfo($auth, $employee);

        $this->assertEquals(
            $str,
            "CRM_TRANSFER,{$auth->guid},{$employee->sip->number}"
        );
    }

    /** @test */
    public function generate_withdrawinfo_as_employee_has_sip(): void
    {
        $sip_auth = $this->sipBuilder->create();
        $auth = $this->employeeBuilder->setSip($sip_auth)->create();
        $this->loginAsEmployee($auth);

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $str = $this->queueService->generateWithdrawinfo($auth, $employee);

        $this->assertEquals(
            $str,
            "CRM_TRANSFER,{$sip_auth->number},{$employee->sip->number}"
        );
    }

    /** @test */
    public function generate_withdrawinfo_as_admin_for_department(): void
    {
        $auth = $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->setData([
            'num' => '67676'
        ])->create();

        $str = $this->queueService->generateWithdrawinfo($auth, $department);

        $this->assertEquals(
            $str,
            "CRM_TRANSFER,admin,{$department->num}"
        );
    }

    /** @test */
    public function generate_withdrawinfo_as_employee_not_sip(): void
    {
        $auth = $this->loginAsEmployee();

        /** @var $department Department */
        $department = $this->departmentBuilder->setData([
            'num' => '67676'
        ])->create();

        $str = $this->queueService->generateWithdrawinfo($auth, $department);

        $this->assertEquals(
            $str,
            "CRM_TRANSFER,{$auth->guid},{$department->num}"
        );
    }

    /** @test */
    public function handler_channel_from_ari_empty(): void
    {
        $res = [];

        $this->assertEmpty(Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $this->assertEmpty(Queue::all());
    }

    /** @test */
    public function handler_channel_from_ari_between_agent(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';
        $uniqueid_2 = 'asterisk-docker01-1683639359.48392';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '370'])->create();

        $department_1 = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($department_2)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('man_2', $sip_2->number);

        $channelName_1 = 'PJSIP/kamailio-000011a6';
        $channelName_2 = 'PJSIP/kamailio-000011a7';

        $state_1 = ChannelStates::RING;
        $state_2 = ChannelStates::RINGING;

        $res = [
            $this->createChannel(
                $caller_2,
                $caller_1,
                $channelName_1,
                '2023-05-09T16:35:59.990+0300',
                state: $state_1,
                accountcode: 'IS_FROM_QUEUE=false',
                id: $uniqueid_1
            ),
            $this->createChannel(
                $caller_1,
                $caller_2,
                $channelName_2,
                '2023-05-09T16:36:00.422+0300',
                state: $state_2,
                accountcode: 'IS_FROM_QUEUE=false'
                ,id: $uniqueid_2
            ),
        ];

        $this->assertEmpty(Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $recs = Queue::all();

        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->employee_id, $employee_1->id);
        $this->assertEquals($recs[0]->department_id, $department_1->id);
        $this->assertEquals($recs[0]->status, QueueStatus::CONNECTION);
        $this->assertEquals($recs[0]->caller_num, $caller_2->number);
        $this->assertEquals($recs[0]->caller_name, $caller_2->name);
        $this->assertEquals($recs[0]->connected_num, $caller_1->number);
        $this->assertEquals($recs[0]->connected_name, $caller_1->name);
        $this->assertEquals($recs[0]->channel, $channelName_1);
        $this->assertEquals($recs[0]->uniqueid, $uniqueid_1);
        $this->assertEquals($recs[0]->type, QueueType::DIAL);
        $this->assertNotNull($recs[0]->connected_at);
        $this->assertNull($recs[0]->called_at);
    }

    /** @test */
    public function handler_channel_from_ari_between_agent_connect(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';
        $uniqueid_2 = 'asterisk-docker01-1683639359.48392';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '370'])->create();

        $department_1 = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($department_2)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('man_2', $sip_2->number);

        $channelName_1 = 'PJSIP/kamailio-000011a6';
        $channelName_2 = 'PJSIP/kamailio-000011a7';

        $res = [
            $this->createChannel(
                $caller_2,
                $caller_1,
                $channelName_1,
                '2023-05-09T16:35:59.990+0300',
                state: ChannelStates::UP,
                accountcode: 'IS_FROM_QUEUE=false',
                id: $uniqueid_1
            ),
            $this->createChannel(
                $caller_1,
                $caller_2,
                $channelName_2,
                '2023-05-09T16:36:00.422+0300',
                state: ChannelStates::RINGING,
                accountcode: 'IS_FROM_QUEUE=false'
                ,id: $uniqueid_2
            ),
        ];

        $this->assertEmpty(Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $recs = Queue::all();

        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->status, QueueStatus::CONNECTION);
        $this->assertNull($recs[0]->called_at);
    }

    /** @test */
    public function handler_channel_from_ari_between_agent_and_client(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';
        $uniqueid_2 = 'asterisk-docker01-1683639359.48392';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();

        $department_1 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('', '44444');

        $channelName_1 = 'PJSIP/kamailio-000011a6';
        $channelName_2 = 'PJSIP/kamailio-000011a7';

        $state_1 = ChannelStates::RING;
        $state_2 = ChannelStates::RINGING;

        $res = [
            $this->createChannel(
                $caller_1,
                $caller_2,
                $channelName_1,
                '2023-05-09T16:35:59.990+0300',
                state: $state_1,
                accountcode: 'IS_FROM_QUEUE=false',
                id: $uniqueid_1
            ),
            $this->createChannel(
                $caller_2,
                $caller_1,
                $channelName_2,
                '2023-05-09T16:36:00.422+0300',
                state: $state_2,
                accountcode: 'IS_FROM_QUEUE=false'
                ,id: $uniqueid_2
            ),
        ];

        $this->assertEmpty(Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $recs = Queue::all();

        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->employee_id, $employee_1->id);
        $this->assertEquals($recs[0]->department_id, $department_1->id);
        $this->assertEquals($recs[0]->status, QueueStatus::CONNECTION);
        $this->assertEquals($recs[0]->caller_num, $caller_1->number);
        $this->assertEquals($recs[0]->caller_name, $caller_1->name);
        $this->assertEquals($recs[0]->connected_num, $caller_2->number);
        $this->assertEquals($recs[0]->connected_name, $caller_2->name);
        $this->assertEquals($recs[0]->channel, $channelName_1);
        $this->assertEquals($recs[0]->uniqueid, $uniqueid_1);
        $this->assertEquals($recs[0]->type, QueueType::DIAL);
        $this->assertNotNull($recs[0]->connected_at);
        $this->assertNull($recs[0]->called_at);
    }

    /** @test */
    public function handler_channel_from_ari_from_ivr(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();

        $department_1 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('', '');

        $channelName_1 = 'PJSIP/kamailio-000011a6';

        $state_1 = ChannelStates::RING;

        $res = [
            $this->createChannel(
                $caller_1,
                $caller_2,
                $channelName_1,
                '2023-05-09T16:35:59.990+0300',
                state: $state_1,
                accountcode: 'IS_FROM_QUEUE=false',
                id: $uniqueid_1
            ),
        ];

        $this->assertEmpty(Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $recs = Queue::all();

        $this->assertCount(1, $recs);

        $this->assertNull($recs[0]->employee_id);
        $this->assertNull($recs[0]->department_id);
        $this->assertEquals($recs[0]->status, QueueStatus::WAIT());
        $this->assertEquals($recs[0]->caller_num, $caller_1->number);
        $this->assertEquals($recs[0]->caller_name, $caller_1->name);
        $this->assertNull($recs[0]->connected_num);
        $this->assertNull($recs[0]->connected_name);
        $this->assertEquals($recs[0]->channel, $channelName_1);
        $this->assertEquals($recs[0]->uniqueid, $uniqueid_1);
        $this->assertEquals($recs[0]->type, QueueType::DIAL);
        $this->assertNull($recs[0]->connected_at);
        $this->assertNull($recs[0]->called_at);
    }

    /** @test */
    public function handler_channel_from_ari_is_from_queue_true(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();

        $department_1 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('', '44444');

        $channelName_1 = 'PJSIP/kamailio-000011a6';

        $state_1 = ChannelStates::RING;

        $queue = $this->queueBuilder
            ->setChannel($channelName_1)
            ->setUniqueid($uniqueid_1)
            ->setStatus(QueueStatus::CONNECTION())
            ->setConnectionNum($caller_1->number)
            ->setConnectionName($caller_1->name)
            ->setFromName($caller_2->name)
            ->setFromNum($caller_2->number)
            ->setType(QueueType::DIAL())
            ->create();

        $res = [
            $this->createChannel(
                $caller_1,
                $caller_2,
                $channelName_1,
                '2023-05-09T16:35:59.990+0300',
                state: $state_1,
                accountcode: 'IS_FROM_QUEUE=true',
                id: $uniqueid_1
            ),
        ];

        $this->assertCount(1, Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $recs = Queue::all();

        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->status, QueueStatus::CANCEL);
    }

    /** @test */
    public function handler_channel_from_ari_dial_update(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';
        $uniqueid_2 = 'asterisk-docker01-1683639359.48392';
        $uniqueid_3 = 'asterisk-docker01-1683639359.48393';
        $uniqueid_4 = 'asterisk-docker01-1683639359.48394';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '370'])->create();

        $department_1 = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($department_2)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('man_2', $sip_2->number);

        $channelName_1 = 'PJSIP/kamailio-000011a6';
        $channelName_2 = 'PJSIP/kamailio-000011a7';

        $queue = $this->queueBuilder
            ->setChannel($channelName_1)
            ->setUniqueid($uniqueid_1)
            ->setStatus(QueueStatus::CONNECTION())
            ->setConnectionNum($caller_1->number)
            ->setConnectionName($caller_1->name)
            ->setFromName($caller_2->name)
            ->setFromNum($caller_2->number)
            ->setType(QueueType::DIAL())
            ->create();

        $this->assertNull($queue->called_at);

        $res = [
            $this->createChannel(
                $caller_2,
                $caller_1,
                $channelName_1,
                '2023-05-09T16:35:59.990+0300',
                state: ChannelStates::UP,
                accountcode: 'IS_FROM_QUEUE=false',
                id: $uniqueid_1
            ),
            $this->createChannel(
                $caller_1,
                $caller_2,
                $channelName_2,
                '2023-05-09T16:36:00.422+0300',
                state: ChannelStates::UP,
                accountcode: 'IS_FROM_QUEUE=false',
                id:$uniqueid_2
            ),
        ];

        $this->assertCount(1, Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $recs = Queue::all();

        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->employee_id, $employee_1->id);
        $this->assertEquals($recs[0]->department_id, $department_1->id);
        $this->assertEquals($recs[0]->status, QueueStatus::TALK());
        $this->assertEquals($recs[0]->caller_num, $caller_2->number);
        $this->assertEquals($recs[0]->caller_name, $caller_2->name);
        $this->assertEquals($recs[0]->connected_num, $caller_1->number);
        $this->assertEquals($recs[0]->connected_name, $caller_1->name);
        $this->assertEquals($recs[0]->channel, $channelName_1);
        $this->assertNotNull($recs[0]->connected_at);
        $this->assertNotNull($recs[0]->called_at);
    }

    /** @test */
    public function handler_channel_from_ari_empty_data(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';
        $uniqueid_2 = 'asterisk-docker01-1683639359.48392';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '370'])->create();

        $department_1 = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($department_2)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('man_2', $sip_2->number);

        $channelName_1 = 'PJSIP/kamailio-000011a6';
        $channelName_2 = 'PJSIP/kamailio-000011a7';

        $queue_1 = $this->queueBuilder
            ->setChannel($channelName_1)
            ->setUniqueid($uniqueid_1)
            ->setStatus(QueueStatus::CONNECTION())
            ->setConnectionNum($caller_1->number)
            ->setConnectionName($caller_1->name)
            ->setFromName($caller_2->name)
            ->setFromNum($caller_2->number)
            ->setType(QueueType::DIAL())
            ->create();

        $queue_2 = $this->queueBuilder
            ->setChannel($channelName_2)
            ->setUniqueid($uniqueid_2)
            ->setStatus(QueueStatus::TALK())
            ->setConnectionNum($caller_1->number)
            ->setConnectionName($caller_1->name)
            ->setFromName($caller_2->name)
            ->setFromNum($caller_2->number)
            ->setType(QueueType::QUEUE())
            ->create();


        $res = [];

        $this->assertEquals(0, Queue::query()->where('status', QueueStatus::CANCEL)->count());

        $this->queueService->handlerChannelFromAri($res);

        $this->assertEquals(2, Queue::query()->where('status', QueueStatus::CANCEL)->count());
    }


    /** @test */
    public function handler_channel_from_ari_dial_transfer_between_agents(): void
    {
        $uniqueid_1 = 'asterisk-docker01-1683639359.48391';
        $uniqueid_2 = 'asterisk-docker01-1683639359.48392';

        $sip_1 = $this->sipBuilder->setData(['number' => '390'])->create();
        $sip_2 = $this->sipBuilder->setData(['number' => '370'])->create();

        $department_1 = $this->departmentBuilder->create();
        $department_2 = $this->departmentBuilder->create();

        $employee_1 = $this->employeeBuilder->setSip($sip_1)->setDepartment($department_1)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->setDepartment($department_2)->create();

        $caller_1 = $this->createCallerID('man_1', $sip_1->number);
        $caller_2 = $this->createCallerID('', '44448');

        $channelName_1 = 'PJSIP/kamailio-000011a6';
        $channelName_2 = 'PJSIP/kamailio-000011a7';

        $queue_1 = $this->queueBuilder
            ->setChannel($channelName_1)
            ->setUniqueid($uniqueid_1)
            ->setStatus(QueueStatus::TALK())
            ->setConnectionNum('321')
            ->setConnectionName('Vika Customer')
            ->setFromName('Victoria Sales')
            ->setFromNum('320')
            ->setType(QueueType::DIAL())
            ->create();

        $res = [
            $this->createChannel(
                $this->createCallerID('Victoria Sales', '320'),
                $this->createCallerID('Victoria Tech', '322'),
                $channelName_1,
                '2023-05-16T06:24:45.337+0000',
                state: ChannelStates::UP,
                accountcode: 'IS_FROM_QUEUE=false',
                id: $uniqueid_1
            ),
            $this->createChannel(
                $this->createCallerID('Victoria Tech', '322'),
                $this->createCallerID('Victoria Sales', '320'),
                $channelName_2,
                '2023-05-16T06:25:04.283+0000',
                state: ChannelStates::RINGING,
                accountcode: 'IS_FROM_QUEUE=false',
                id:$uniqueid_2
            ),
        ];

        $this->assertCount(1, Queue::all());

        $this->queueService->handlerChannelFromAri($res);

        $recs = Queue::all();

        $this->assertCount(1, $recs);

        $this->assertEquals($recs[0]->status, QueueStatus::CONNECTION());
        $this->assertEquals($recs[0]->connected_num, '322');
        $this->assertEquals($recs[0]->channel, $channelName_1);
        $this->assertNotNull($recs[0]->connected_at);
    }
}
