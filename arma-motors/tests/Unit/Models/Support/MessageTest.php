<?php

namespace Tests\Unit\Models\Support;

use App\Models\Support\Message;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function list_status()
    {
        $this->assertNotEmpty(Message::statusList());
        $this->assertIsArray(Message::statusList());
        $this->assertCount(3, Message::statusList());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function status_assert_ok()
    {
        Message::assertStatus(Message::STATUS_READ);
    }

    /** @test */
    public function status_assert_fail()
    {
        $this->expectException(\InvalidArgumentException::class);

        Message::assertStatus('wrong');
    }

    /** @test */
    public function status_check_ok()
    {
        $this->assertTrue(Message::checkStatus(Message::STATUS_READ));
    }

    /** @test */
    public function status_check_fail()
    {
        $this->assertFalse(Message::checkStatus('wrong'));
    }
}



