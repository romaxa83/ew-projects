<?php

namespace Tests\Unit\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Enums\Employees\Status;
use App\Models\Calls\Queue;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\PAMI\Listener\QueueStatusListener;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Kamailio\LocationBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;
use Tests\Traits\AmiEventHelper;

class QueueStatusListenerTest extends TestCase
{
    use DatabaseTransactions;
    use AmiEventHelper;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;
    protected LocationBuilder $locationBuilder;
    protected QueueBuilder $queueBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->locationBuilder = resolve(LocationBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }

    /** @test */
    public function event_send_status_1(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setStatus(Status::ERROR())
            ->setSip($sip)->create();

        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::TALK())
            ->create();

        $event = $this->createQueueMemberStatusEvent(
            status: '1',
            memberName: $sip->number
        );

        $listener = new QueueStatusListener();
        $listener->handle($event);

        $employee->refresh();
        $queue->refresh();

        $this->assertTrue($employee->status->isFree());
    }

    /** @test */
    public function event_send_status_2_as_talk(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setStatus(Status::ERROR())
            ->setSip($sip)->create();

        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::CONNECTION())
            ->create();

        $event = $this->createQueueMemberStatusEvent(
            status: '2',
            memberName: $sip->number
        );

        $this->assertNull($queue->called_at);

        $listener = new QueueStatusListener();
        $listener->handle($event);

        $employee->refresh();
        $queue->refresh();

        $this->assertTrue($employee->status->isTalk());

        $this->assertTrue($queue->status->isTalk());
        $this->assertNotNull($queue->called_at);
    }

    /** @test */
    public function event_send_status_paused_true(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setStatus(Status::ERROR())
            ->setSip($sip)->create();

        $event = $this->createQueueMemberStatusEvent(
            status: '2',
            memberName: $sip->number,
            paused: '1'
        );

        $listener = new QueueStatusListener();
        $listener->handle($event);

        $employee->refresh();

        $this->assertTrue($employee->status->isPause());
    }
}
