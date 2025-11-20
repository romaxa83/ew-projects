<?php

namespace Tests\Unit\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Models\Calls\Queue;
use App\Models\Sips\Sip;
use App\PAMI\Listener\DialListener;
use App\PAMI\Message\Event\DialEndEvent;
use App\PAMI\Message\Event\DialStateEvent;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;
use Tests\Traits\AmiEventHelper;

class DialListenerTest extends TestCase
{
    use DatabaseTransactions;
    use AmiEventHelper;
    use WithFaker;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected QueueBuilder $queueBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }

    /** @test */
    public function listen_dial_state_and_change_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();

        $employee = $this->employeeBuilder->setSip($sip)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->create();

        $channel = $this->faker->uuid;

        $date = CarbonImmutable::now();
        $connected_at = $date->subMinutes(3);
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setEmployee($employee)
            ->setDepartment($employee->department)
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::WAIT())
            ->setChannel($channel)
            ->setConnectedAt($connected_at)
            ->setCalledAt($date->subMinutes(2))
            ->create();

        $event = $this->createDialStateEvent(
            channel: $channel,
            connectedLineNum: $sip_2->number,
            connectedLineName: $employee_2->getName()
        );

        $listener = new DialListener();
        $listener->handle($event);

        $queue->refresh();

        $this->assertTrue($queue->status->isConnection());
        $this->assertNull($queue->called_at);
        $this->assertEquals($queue->employee_id, $employee_2->id);
        $this->assertEquals($queue->department_id, $employee_2->department_id);
        $this->assertEquals($queue->connected_num, $event->getConnectedLineNum());
        $this->assertEquals($queue->connected_name, $event->getConnectedLineName());
        $this->assertNotEquals(
            $queue->connected_at->format('Y-m-d H:i:s'),
            $connected_at->format('Y-m-d H:i:s')
        );
    }

    /** @test */
    public function listen_dial_state_and_change_queue_not_sip(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();

        $employee = $this->employeeBuilder->setSip($sip)->create();
        $employee_2 = $this->employeeBuilder->setSip($sip_2)->create();

        $channel = $this->faker->uuid;

        $date = CarbonImmutable::now();
        $connected_at = $date->subMinutes(3);
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setEmployee($employee)
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::WAIT())
            ->setChannel($channel)
            ->setConnectedAt($connected_at)
            ->setCalledAt($date->subMinutes(2))
            ->create();

        $event = $this->createDialStateEvent(
            channel: $channel,
            connectedLineNum: '404',
            connectedLineName: 'oop'
        );

        $listener = new DialListener();
        $listener->handle($event);

        $queue->refresh();

        $this->assertTrue($queue->status->isConnection());
        $this->assertNull($queue->called_at);
        $this->assertEquals($queue->employee_id, $employee->id);
        $this->assertEquals($queue->connected_num, $event->getConnectedLineNum());
        $this->assertEquals($queue->connected_name, $event->getConnectedLineName());
        $this->assertNotEquals(
            $queue->connected_at->format('Y-m-d H:i:s'),
            $connected_at->format('Y-m-d H:i:s')
        );
    }

    /** @test */
    public function listen_dial_state_and_no_change_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $channel = $this->faker->uuid;

        $date = CarbonImmutable::now();
        $connected_at = $date->subMinutes(3);
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::WAIT())
            ->setChannel($channel)
            ->setConnectedAt($connected_at)
            ->setCalledAt($date->subMinutes(2))
            ->create();

        $event = $this->createDialStateEvent(
            channel: $channel,
            dialStatus: DialStateEvent::DIAL_STATUS_PROCEEDING
        );

        $listener = new DialListener();
        $listener->handle($event);

        $queue->refresh();

        $this->assertFalse($queue->status->isConnection());
        $this->assertNotNull($queue->called_at);
        $this->assertEquals(
            $queue->connected_at->format('Y-m-d H:i:s'),
            $connected_at->format('Y-m-d H:i:s')
        );
    }

    /** @test */
    public function listen_dial_end_and_change_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $channel = $this->faker->uuid;

        $date = CarbonImmutable::now();
        $connected_at = $date->subMinutes(3);
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::CONNECTION())
            ->setChannel($channel)
            ->setConnectedAt($connected_at)
            ->create();

        $this->assertNull($queue->called_at);

        $event = $this->createDialEndEvent(
            channel: $channel,
            dialStatus: DialEndEvent::DIAL_STATUS_ANSWER
        );

        $listener = new DialListener();
        $listener->handle($event);

        $queue->refresh();

        $this->assertTrue($queue->status->isTalk());
        $this->assertNotNull($queue->called_at);
    }

    /** @test */
    public function listen_dial_end_and_not_change_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $channel = $this->faker->uuid;

        $date = CarbonImmutable::now();
        $connected_at = $date->subMinutes(3);
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::CONNECTION())
            ->setChannel($channel)
            ->setConnectedAt($connected_at)
            ->create();

        $this->assertNull($queue->called_at);

        $event = $this->createDialEndEvent(
            channel: $channel,
            dialStatus: DialEndEvent::DIAL_STATUS_CANCEL
        );

        $listener = new DialListener();
        $listener->handle($event);

        $queue->refresh();

        $this->assertFalse($queue->status->isTalk());
        $this->assertNull($queue->called_at);
    }
}
