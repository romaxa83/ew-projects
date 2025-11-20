<?php

namespace Tests\Unit\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Models\Calls\Queue;
use App\Models\Sips\Sip;
use App\PAMI\Listener\HangupListener;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;
use Tests\Traits\AmiEventHelper;

class HangupListenerTest extends TestCase
{
    use DatabaseTransactions;
    use AmiEventHelper;
    use WithFaker;

    protected EmployeeBuilder $employeeBuilder;
    protected QueueBuilder $queueBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }

    /** @test */
    public function listen_hangup_and_change_queue(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $channel = $this->faker->uuid;

        $date = CarbonImmutable::now();
        $connected_at = $date->subMinutes(3);
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::TALK())
            ->setChannel($channel)
            ->setConnectedAt($connected_at)
            ->setCalledAt($date->subMinutes(2))
            ->create();

        $event = $this->createHangupEvent(
            channel: $channel,
        );

        $listener = new HangupListener();
        $listener->handle($event);

        $queue->refresh();

        $this->assertTrue($queue->status->isCancel());
    }
}
