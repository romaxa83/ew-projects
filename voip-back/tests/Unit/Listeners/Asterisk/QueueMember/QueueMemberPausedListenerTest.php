<?php

namespace Tests\Unit\Listeners\Asterisk\QueueMember;

use App\IPTelephony\Events\QueueMember\QueueMemberPausedEvent;
use App\IPTelephony\Listeners\QueueMember\QueueMemberPausedListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Asterisk\QueueMemberBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueMemberPausedListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected QueueMemberService $queueMemberService;
    protected QueueMemberBuilder $queueMemberBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->queueMemberService = resolve(QueueMemberService::class);
        $this->queueMemberBuilder = resolve(QueueMemberBuilder::class);
    }

//    /** @test */
//    public function success_paused_true()
//    {
//        /** @var $sip Sip */
//        $sip = $this->sipBuilder->create();
//        /** @var $employee Employee */
//        $employee = $this->employeeBuilder->setSip($sip)->create();
//
//        $subscriber = $this->queueMemberBuilder->setEmployee($employee)->create();
//
//        $this->assertNull($subscriber->paused);
//
//        $event = new QueueMemberPausedEvent($employee, true);
//        $listener = resolve(QueueMemberPausedListener::class);
//        $listener->handle($event);
//
//        $subscriberUpdate = $this->queueMemberService->getBy('uuid', $employee->guid);
//
//        $this->assertTrue((bool)$subscriberUpdate->paused);
//
//        $this->assertEquals($subscriberUpdate->queue_name, $subscriber->queue_name);
//    }

    /** @test */
    public function success_paused_false()
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $subscriber = $this->queueMemberBuilder->setEmployee($employee)->create();

        $this->assertNull($subscriber->paused);

        $event = new QueueMemberPausedEvent($employee, false);
        $listener = resolve(QueueMemberPausedListener::class);
        $listener->handle($event);

        $subscriberUpdate = $this->queueMemberService->getBy('uuid', $employee->guid);

        $this->assertFalse((bool)$subscriberUpdate->paused);

        $this->assertEquals($subscriberUpdate->queue_name, $subscriber->queue_name);
    }
}
