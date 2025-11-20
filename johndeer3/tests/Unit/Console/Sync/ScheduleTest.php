<?php

namespace Tests\Unit\Console\Sync;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_timer_for_command_schedule(): void
    {
        /** @var \Illuminate\Console\Scheduling\Schedule $schedule */
        $schedule = app()->make(\Illuminate\Console\Scheduling\Schedule::class);

        $events = collect($schedule->events())->filter(function (\Illuminate\Console\Scheduling\Event $event) {
            return stripos($event->command, 'jd:sync');
        });

        $this->assertCount(1, $events);

        $events->each(function (\Illuminate\Console\Scheduling\Event $event) {
            $this->assertEquals('0 0 * * *', $event->expression);
        });
    }
}

