<?php

namespace Tests\Unit\Commands\Workers;

use App\Enums\Formats\DayEnum;
use App\Models\Schedules\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Kamailio\LocationBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class HoldMusic extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function hold_music(): void
    {
        /** @var $model Schedule */
        $model = Schedule::all()->first();
        $scheduleMonday = $model->days()->where('name', DayEnum::MONDAY)->first();
        $scheduleMonday->end_work_time = '18:00';
        $scheduleMonday->save();

        // monday
        $date = new CarbonImmutable('2022-10-3 17:30:00');
        CarbonImmutable::setTestNow($date);

        $this->artisan('workers:hold_music');

    }
}

