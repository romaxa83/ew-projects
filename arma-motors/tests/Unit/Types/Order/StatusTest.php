<?php

namespace Tests\Unit\Types\Order;

use App\Traits\AssetData;
use App\Types\Order\Status;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use AssetData;

    /** @test */
    public function get_list()
    {
        $this->assertNotEmpty(Status::list());
        $this->assertIsArray(Status::list());
        $this->assertCount(6, Status::list());

        $this->assertEquals(Status::list()[Status::DRAFT], __('translation.order.status.draft'));
        $this->assertEquals(Status::list()[Status::CREATED], __('translation.order.status.created'));
        $this->assertEquals(Status::list()[Status::IN_PROCESS], __('translation.order.status.in_process'));
        $this->assertEquals(Status::list()[Status::DONE], __('translation.order.status.done'));
        $this->assertEquals(Status::list()[Status::CLOSE], __('translation.order.status.close'));
        $this->assertEquals(Status::list()[Status::REJECT], __('translation.order.status.reject'));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function assert_ok()
    {
        Status::assert(Status::REJECT);
    }

    /** @test */
    public function assert_fail()
    {
        $wrongState = 'wrong';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid order status', ['status' => $wrongState]));

        Status::assert($wrongState);
    }

    /** @test */
    public function check_ok()
    {
        $this->assertTrue(Status::check(Status::REJECT));
    }

    /** @test */
    public function check_fail()
    {
        $this->assertFalse(Status::check('wrong'));
    }

    /** @test */
    public function check_is_close_status()
    {
        $this->assertTrue(Status::isCloseStatus(Status::CLOSE));
        $this->assertFalse(Status::isCloseStatus(Status::DONE));
    }

    /** @test */
    public function check_is_draft_status()
    {
        $this->assertTrue(Status::isDraftStatus(Status::DRAFT));
        $this->assertFalse(Status::isDraftStatus(Status::DONE));
    }

    /** @test */
    public function check_is_create_status()
    {
        $this->assertTrue(Status::isCreateStatus(Status::CREATED));
        $this->assertFalse(Status::isCreateStatus(Status::DONE));
    }

    /** @test */
    public function check_is_process_status()
    {
        $this->assertTrue(Status::isProcessStatus(Status::IN_PROCESS));
        $this->assertFalse(Status::isProcessStatus(Status::DONE));
    }

    /** @test */
    public function create_success_draft()
    {
        $status = Status::create(Status::DRAFT);

        $this->assertTrue($status instanceof Status);
        $this->assertEquals($status->getValue(), Status::DRAFT);
        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isClose());
    }

    /** @test */
    public function create_success_created()
    {
        $status = Status::create(Status::CREATED);

        $this->assertTrue($status instanceof Status);
        $this->assertEquals($status->getValue(), Status::CREATED);
        $this->assertTrue($status->isCreated());
        $this->assertFalse($status->isClose());
    }

    /** @test */
    public function create_success_in_process()
    {
        $status = Status::create(Status::IN_PROCESS);

        $this->assertTrue($status instanceof Status);
        $this->assertEquals($status->getValue(), Status::IN_PROCESS);
        $this->assertTrue($status->isProcess());
        $this->assertFalse($status->isClose());
    }

    /** @test */
    public function create_success_done()
    {
        $status = Status::create(Status::DONE);

        $this->assertTrue($status instanceof Status);
        $this->assertEquals($status->getValue(), Status::DONE);
        $this->assertTrue($status->isDone());
        $this->assertFalse($status->isClose());
    }

    /** @test */
    public function create_success_close()
    {
        $status = Status::create(Status::CLOSE);

        $this->assertTrue($status instanceof Status);
        $this->assertEquals($status->getValue(), Status::CLOSE);
        $this->assertTrue($status->isClose());
        $this->assertFalse($status->isCreated());
    }

    /** @test */
    public function create_success_reject()
    {
        $status = Status::create(Status::REJECT);

        $this->assertTrue($status instanceof Status);
        $this->assertEquals($status->getValue(), Status::REJECT);
        $this->assertTrue($status->isReject());
        $this->assertFalse($status->isClose());
    }

    /** @test */
    public function create_fail()
    {
        $wrongState = 'wrong';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid order status', ['status' => $wrongState]));

        Status::create($wrongState);
    }

    /** @test */
    public function current_status()
    {
        $statuses = Status::statusForCurrent();

        $this->assertIsArray($statuses);
        $this->assertCount(4, $statuses);

        $this->assertTrue(in_array(Status::DRAFT, $statuses));
        $this->assertTrue(in_array(Status::IN_PROCESS, $statuses));
        $this->assertTrue(in_array(Status::CREATED, $statuses));
        $this->assertTrue(in_array(Status::DONE, $statuses));
    }

    /** @test */
    public function history_status()
    {
        $statuses = Status::statusForHistory();

        $this->assertIsArray($statuses);
        $this->assertCount(1, $statuses);

        $this->assertTrue(in_array(Status::CLOSE, $statuses));
    }
}
