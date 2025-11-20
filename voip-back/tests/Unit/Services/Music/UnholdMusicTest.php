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

class UnholdMusicTest extends TestCase
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
    public function success_unhold_music(): void
    {
        /** @var $model Schedule */
        $model = Schedule::all()->first();
        $scheduleMonday = $model->days()->where('name', DayEnum::MONDAY)->first();
        $scheduleMonday->end_work_time = '18:00';
        $scheduleMonday->save();

        // monday
        $date = new CarbonImmutable('2022-10-3 18:01:00');
        CarbonImmutable::setTestNow($date);

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $data = [
            'periodic_announce' => 'ter',
            'periodic_announce_frequency' => 232,
            'relative_periodic_announce' => 'no'
        ];
        /** @var $model Music */
        $model = $this->musicBuilder->withRecord()
            ->department($department)
            ->unholdData($data)
            ->create();

        $queue = $this->queueBuilder
            ->department($department)
            ->create();

        $this->assertNotEquals($queue->periodic_announce, $data['periodic_announce']);
        $this->assertNotEquals($queue->periodic_announce_frequency, $data['periodic_announce_frequency']);
        $this->assertNotEquals($queue->relative_periodic_announce, $data['relative_periodic_announce']);


        $this->musicService->unholdMusicToEndWorkDay();

        $model->refresh();

        $this->assertFalse($model->has_unhold_data);
        $this->assertEmpty($model->unhold_data);

        $queue = $this->queueService->getBy('uuid', $model->department->guid);

        $this->assertEquals($queue->periodic_announce, $data['periodic_announce']);
        $this->assertEquals($queue->periodic_announce_frequency, $data['periodic_announce_frequency']);
        $this->assertEquals($queue->relative_periodic_announce, $data['relative_periodic_announce']);

        // yet
//        $this->musicService->holdMusicToEndWorkDay();
    }


}

