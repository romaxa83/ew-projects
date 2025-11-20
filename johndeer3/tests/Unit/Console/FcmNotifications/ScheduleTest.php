<?php

namespace Tests\Unit\Console\FcmNotifications;

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
            return stripos($event->command, 'jd:report:push');
        });

        $this->assertCount(3, $events);

        $events->each(function (\Illuminate\Console\Scheduling\Event $event) {
            $command = last(explode(' ', $event->command));
            if($command == 'jd:report:push'){
                $this->assertEquals('0 10 * * *', $event->expression);
            }
            if($command == 'jd:report:push-in-start-day'){
                $this->assertEquals('0 9 * * 1-5', $event->expression);
            }
            if($command == 'jd:report:push-in-end-day'){
                $this->assertEquals('0 18 * * 1-5', $event->expression);
            }
        });
    }
}
