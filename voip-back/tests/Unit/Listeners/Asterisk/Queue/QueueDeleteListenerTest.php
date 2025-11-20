<?php

namespace Tests\Unit\Listeners\Asterisk\Queue;

use App\Events\Departments\DepartmentCreatedEvent;
use App\IPTelephony\Events\Queue\QueueDeleteEvent;
use App\IPTelephony\Listeners\Queue\QueueDeleteListener;
use App\IPTelephony\Listeners\Queue\QueueInsertListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\TestCase;

class QueueDeleteListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected QueueService $queueService;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->queueService = resolve(QueueService::class);
    }

    /** @test */
    public function success_delete()
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->assertFalse($department->is_insert_asterisk);

        $event = new DepartmentCreatedEvent($department);
        $listener = resolve(QueueInsertListener::class);
        $listener->handle($event);

        $department->refresh();

        $this->assertTrue($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertNotNull($queue);

        $event = new QueueDeleteEvent($department);
        $listener = resolve(QueueDeleteListener::class);
        $listener->handle($event);

        $department->refresh();

        $this->assertFalse($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertNull($queue);
    }
}
