<?php

namespace Tests\Unit\Services\Communications\IncomingCallService;

use App\Models\Calls\IncomingCall;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Zadarma\CallsEvents;
use App\Services\Communications\IncomingCallService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\IncomingCallBuilder;
use Tests\Builders\Ringostat\EventAfterCallBuilder;
use Tests\Builders\Zadarma\CallEventBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;


    protected EventAfterCallBuilder $eventAfterCallBuilder;
    protected CallEventBuilder $callBuilder;
    protected IncomingCallBuilder $incomingCallBuilder;

    public function setUp(): void
    {
        $this->eventAfterCallBuilder = resolve(EventAfterCallBuilder::class);
        $this->callBuilder = resolve(CallEventBuilder::class);
        $this->incomingCallBuilder = resolve(IncomingCallBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function delete_success()
    {
        /** @var $event CallsEvents */
        $event = $this->callBuilder->create();

        $this->incomingCallBuilder
            ->call_id($event->pbx_call_id)
            ->create();

        $res = IncomingCallService::delete($event);

        $this->assertTrue($res);
        $this->assertEquals(0, IncomingCall::count());
    }

    /** @test */
    public function delete_success_as_ringostat()
    {
        /** @var $event EventAfterCall */
        $event = $this->eventAfterCallBuilder->create();

        $this->incomingCallBuilder
            ->call_id($event->call_id)
            ->create();

        $res = IncomingCallService::delete($event);

        $this->assertTrue($res);
        $this->assertEquals(0, IncomingCall::count());
    }

    /** @test */
    public function delete_but_not_call()
    {
        /** @var $event EventAfterCall */
        $event = $this->eventAfterCallBuilder->create();

        $res = IncomingCallService::delete($event);

        $this->assertFalse($res);
    }
}
