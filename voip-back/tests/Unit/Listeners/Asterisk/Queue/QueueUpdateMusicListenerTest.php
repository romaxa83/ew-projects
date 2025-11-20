<?php

namespace Tests\Unit\Listeners\Asterisk\Queue;

use App\Helpers\DbConnections;
use App\IPTelephony\Events\Queue\QueueUpdateMusicEvent;
use App\IPTelephony\Listeners\Queue\QueueUpdateMusicListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Asterisk\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;

class QueueUpdateMusicListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public array $connectionsToTransact = [
        DbConnections::DEFAULT,
        DbConnections::ASTERISK,
    ];

    protected DepartmentBuilder $departmentBuilder;
    protected MusicBuilder $musicBuilder;
    protected QueueBuilder $queueBuilder;
    protected QueueService $queueService;


    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->queueService = resolve(QueueService::class);
        $this->musicBuilder = resolve(MusicBuilder::class);
        $this->queueBuilder = resolve(QueueBuilder::class);
    }

    /** @test */
    public function success_update_data()
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->setData(['num' => 99991])->create();

        $model = $this->musicBuilder->withRecord()->department($department)->create();

        $queue = $this->queueBuilder
            ->department($department)
            ->relativePeriodicAnnounce('no')
            ->create();

        $this->assertNotEquals($queue->periodic_announce, $model->media->first()->name);
        $this->assertNotEquals($queue->periodic_announce_frequency, $model->interval);
        $this->assertNotEquals($queue->relative_periodic_announce, 'yes');

        $event = new QueueUpdateMusicEvent($model);
        $listener = resolve(QueueUpdateMusicListener::class);
        $listener->handle($event);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertEquals($queue->periodic_announce, config('asterisk.music.prefix_for_name'). $model->media->first()->name);
        $this->assertEquals($queue->periodic_announce_frequency, $model->interval);
        $this->assertEquals($queue->relative_periodic_announce, 'yes');
    }
}

