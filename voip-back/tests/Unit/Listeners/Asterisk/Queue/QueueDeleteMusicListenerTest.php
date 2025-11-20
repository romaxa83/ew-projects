<?php

namespace Tests\Unit\Listeners\Asterisk\Queue;

use App\Helpers\DbConnections;
use App\IPTelephony\Events\Queue\QueueDeleteMusicEvent;
use App\IPTelephony\Listeners\Queue\QueueDeleteMusicListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Asterisk\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;

class QueueDeleteMusicListenerTest extends TestCase
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
            ->relativePeriodicAnnounce('yes')
            ->periodicAnnounce('test')
            ->periodicAnnounceFrequency(11)
            ->create();

        $this->assertNotNull($queue->periodic_announce);
        $this->assertNotNull($queue->periodic_announce_frequency);
        $this->assertNotNull($queue->relative_periodic_announce);

        $event = new QueueDeleteMusicEvent($model);
        $listener = resolve(QueueDeleteMusicListener::class);
        $listener->handle($event);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertNull($queue->periodic_announce);
        $this->assertNull($queue->periodic_announce_frequency);
        $this->assertNull($queue->relative_periodic_announce);
    }
}


