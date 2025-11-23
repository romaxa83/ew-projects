<?php

namespace Tests\Unit\Services\Calendar\CalendarService;

use App\Services\Calendars\CalendarService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\StatusBuilder;
use Tests\TestCase;

class InitCalendarStatusesTest extends TestCase
{
    use DatabaseTransactions;

    protected StatusBuilder $statusBuilder;
    protected CalendarService $service;

    public function setUp(): void
    {
        $this->statusBuilder = resolve(StatusBuilder::class);
        $this->service = resolve(CalendarService::class);


        parent::setUp();
    }

    /** @test */
    public function success()
    {
        $status_1 = $this->statusBuilder
            ->actions(['disable_dispatch'])
            ->asBooked()
            ->create();
        $status_2 = $this->statusBuilder
            ->actions(['enable_dispatch'])
            ->asDuplicate()
            ->create();
        $status_3 = $this->statusBuilder
            ->actions(['enable_dispatch'])
            ->asNewLead()
            ->create();

        $result = $this->service->inCalendarStatuses();

        $this->assertFalse(in_array($status_1->id, $result));
        $this->assertTrue(in_array($status_2->id, $result));
        $this->assertTrue(in_array($status_3->id, $result));
    }
}
