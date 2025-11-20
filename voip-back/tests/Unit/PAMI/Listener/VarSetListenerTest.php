<?php

namespace Tests\Unit\PAMI\Listener;

use App\Enums\Calls\QueueStatus;
use App\Enums\Employees\Status;
use App\Models\Calls\Queue;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\PAMI\Listener\VarSetListener;
use App\PAMI\Message\Event\VarSetEvent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;
use Tests\Traits\AmiEventHelper;

class VarSetListenerTest extends TestCase
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
    public function queue_cancel_success(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setStatus(Status::ERROR())
            ->setSip($sip)->create();

        $channel = $this->faker->uuid;

        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectionNum($sip->number)
            ->setStatus(QueueStatus::TALK())
            ->setChannel($channel)
            ->create();

        $event = $this->createVarSetEvent(
            chanel: $channel,
            variable: VarSetEvent::DESTROY,
            value: '1'
        );

        $listener = new VarSetListener();
        $listener->handle($event);

        $queue->refresh();

        $this->assertTrue($queue->status->isCancel());
    }
}
