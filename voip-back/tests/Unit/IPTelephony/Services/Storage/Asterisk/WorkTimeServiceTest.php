<?php

namespace Tests\Unit\IPTelephony\Services\Storage\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\WorkTimeService;
use App\Models\Schedules\Schedule;
use Carbon\CarbonImmutable;
use Tests\Builders\Schedules\AdditionDayBuilder;
use Tests\TestCase;

class WorkTimeServiceTest extends TestCase
{
    private WorkTimeService $service;
    protected AdditionDayBuilder $additionDayBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(WorkTimeService::class);
        $this->additionDayBuilder = resolve(AdditionDayBuilder::class);
    }

    /** @test */
    public function check_addition_day_is_not_work(): void
    {
        /** @var $schedule Schedule */
        $schedule = Schedule::query()->first();

        $now = CarbonImmutable::now();

        $this->additionDayBuilder
            ->setStartAt($now->startOfDay())
            ->setSchedule($schedule)
            ->create();

        $this->assertEmpty($this->service->getAll());

        $this->service->setIsWorkTime();

        $data = $this->service->getAll();

        $this->assertNotEmpty($data);
        $this->assertEquals($data[0]->date, $now);
        $this->assertEquals($data[0]->work_status, 0);
    }

    /** @test */
    public function check_addition_period_day_is_not_work(): void
    {
        /** @var $schedule Schedule */
        $schedule = Schedule::query()->first();

        $now = CarbonImmutable::now();

        $this->additionDayBuilder
            ->setStartAt($now->subDay()->startOfDay())
            ->setEndAt($now->addDay()->startOfDay())
            ->setSchedule($schedule)
            ->create();

        $this->assertEmpty($this->service->getAll());

        $this->service->setIsWorkTime();

        $data = $this->service->getAll();

        $this->assertNotEmpty($data);
        $this->assertEquals($data[0]->date, $now);
        $this->assertEquals($data[0]->work_status, 0);
    }

    /** @test */
    public function check_day_is_work(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::create(
                CarbonImmutable::now()->year,
                CarbonImmutable::now()->month,
                CarbonImmutable::now()->day,
                12,
                20,
            )
        );

        /** @var $schedule Schedule */
        $schedule = Schedule::query()->first();

        $now = CarbonImmutable::now();

        $day = $schedule->days->where('name', strtolower($now->isoFormat('dddd')))->first();
        $day->start_work_time = '7:30';
        $day->end_work_time = '17:00';
        $day->save();

        $this->assertEmpty($this->service->getAll());

        $this->service->setIsWorkTime();

        $data = $this->service->getAll();

        $this->assertNotEmpty($data);
        $this->assertEquals($data[0]->date, $now);
        $this->assertEquals($data[0]->work_status, 1);
    }

    /** @test */
    public function check_day_is_not_work(): void
    {
        CarbonImmutable::setTestNow(
            CarbonImmutable::create(
                CarbonImmutable::now()->year,
                CarbonImmutable::now()->month,
                CarbonImmutable::now()->day,
                2,
                20,
            )
        );

        /** @var $schedule Schedule */
        $schedule = Schedule::query()->first();

        $now = CarbonImmutable::now();

        $day = $schedule->days->where('name', strtolower($now->isoFormat('dddd')))->first();
        $day->start_work_time = '7:30';
        $day->end_work_time = '17:00';
        $day->save();

        $this->assertEmpty($this->service->getAll());

        $this->service->setIsWorkTime();

        $data = $this->service->getAll();

        $this->assertNotEmpty($data);
        $this->assertEquals($data[0]->date, $now);
        $this->assertEquals($data[0]->work_status, 0);
    }

    /** @test */
    public function check_day_start_work_time_is_not_set(): void
    {
        /** @var $schedule Schedule */
        $schedule = Schedule::query()->first();

        $now = CarbonImmutable::now();

        $day = $schedule->days->where('name', strtolower($now->isoFormat('dddd')))->first();
        $day->start_work_time = null;
        $day->end_work_time = '17:00';
        $day->save();

        $this->assertEmpty($this->service->getAll());

        $this->service->setIsWorkTime();

        $data = $this->service->getAll();

        $this->assertNotEmpty($data);
        $this->assertEquals($data[0]->date, $now);
        $this->assertEquals($data[0]->work_status, 0);
    }

    /** @test */
    public function check_day_end_work_time_is_not_set(): void
    {
        /** @var $schedule Schedule */
        $schedule = Schedule::query()->first();

        $now = CarbonImmutable::now();

        $day = $schedule->days->where('name', strtolower($now->isoFormat('dddd')))->first();
        $day->start_work_time = '7:00';
        $day->end_work_time = null;
        $day->save();

        $this->assertEmpty($this->service->getAll());

        $this->service->setIsWorkTime();

        $data = $this->service->getAll();

        $this->assertNotEmpty($data);
        $this->assertEquals($data[0]->date, $now);
        $this->assertEquals($data[0]->work_status, 0);
    }

    /** @test */
    public function check_day_is_not_active(): void
    {
        /** @var $schedule Schedule */
        $schedule = Schedule::query()->first();

        $now = CarbonImmutable::now();

        $day = $schedule->days->where('name', strtolower($now->isoFormat('dddd')))->first();
        $day->start_work_time = '7:00';
        $day->end_work_time = '17:00';
        $day->active = false;
        $day->save();

        $this->assertEmpty($this->service->getAll());

        $this->service->setIsWorkTime();

        $data = $this->service->getAll();

        $this->assertNotEmpty($data);
        $this->assertEquals($data[0]->date, $now);
        $this->assertEquals($data[0]->work_status, 0);
    }
}

