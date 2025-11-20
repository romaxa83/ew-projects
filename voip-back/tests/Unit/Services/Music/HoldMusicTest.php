<?php

namespace Tests\Unit\Services\Music;

use App\Enums\Formats\DayEnum;
use App\Helpers\DbConnections;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use App\Models\Musics\Music;
use App\Models\Schedules\Schedule;
use App\Services\Musics\MusicService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Asterisk\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;

class HoldMusicTest extends TestCase
{
    use DatabaseTransactions;
    public array $connectionsToTransact = [
        DbConnections::DEFAULT,
        DbConnections::ASTERISK,
    ];

    protected MusicBuilder $musicBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected QueueBuilder $queueBuilder;

    protected MusicService $musicService;
    protected QueueService $queueService;
    protected function setUp(): void
    {
        parent::setUp();

        $this->musicBuilder = resolve( MusicBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);

        $this->musicService = resolve(MusicService::class);
        $this->queueService = resolve(QueueService::class);
    }

    /** @test */
    public function success_hold_music(): void
    {
        /** @var $model Schedule */
        $model = Schedule::all()->first();
        $scheduleMonday = $model->days()->where('name', DayEnum::MONDAY)->first();
        $scheduleMonday->end_work_time = '18:00';
        $scheduleMonday->save();

        // monday
        $date = new CarbonImmutable('2022-10-3 17:30:00');
        CarbonImmutable::setTestNow($date);

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Music */
        $model = $this->musicBuilder->withRecord()->department($department)->create();

        $queue = $this->queueBuilder
            ->department($department)
            ->relativePeriodicAnnounce('yes')
            ->periodicAnnounce('test')
            ->periodicAnnounceFrequency(11)
            ->create();

        $this->assertFalse($model->has_unhold_data);
        $this->assertEmpty($model->unhold_data);

        $this->musicService->holdMusicToEndWorkDay();

        $model->refresh();

        $this->assertTrue($model->has_unhold_data);
        $this->assertEquals($model->unhold_data['periodic_announce'], $queue->periodic_announce);
        $this->assertEquals($model->unhold_data['periodic_announce_frequency'], $queue->periodic_announce_frequency);
        $this->assertEquals($model->unhold_data['relative_periodic_announce'], $queue->relative_periodic_announce);

        $queue = $this->queueService->getBy('uuid', $model->department->guid);

        $this->assertNull($queue->periodic_announce);
        $this->assertNull($queue->periodic_announce_frequency);
        $this->assertNull($queue->relative_periodic_announce);
    }


}
