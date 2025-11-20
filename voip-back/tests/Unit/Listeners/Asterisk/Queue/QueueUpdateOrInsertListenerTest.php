<?php

namespace Tests\Unit\Listeners\Asterisk\Queue;

use App\IPTelephony\Events\Queue\QueueUpdateOrCreateEvent;
use App\IPTelephony\Listeners\Queue\QueueUpdateOrInsertListener;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\TestCase;

class QueueUpdateOrInsertListenerTest extends TestCase
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
    public function success_insert()
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->setData(['num' => 99991])->create();

        $this->assertFalse($department->is_insert_asterisk);

        $event = new QueueUpdateOrCreateEvent($department);
        $listener = resolve(QueueUpdateOrInsertListener::class);
        $listener->handle($event);

        $department->refresh();

        $this->assertTrue($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertEquals($queue->name, $department->name);
        $this->assertEquals($queue->uuid, $department->guid);
        $this->assertEquals($queue->num, $department->num);
        $this->assertEquals($queue->musiconhold, config('asterisk.queue.musiconhold'));
        $this->assertEquals($queue->timeout, config('asterisk.queue.timeout'));
        $this->assertEquals($queue->timeout, config('asterisk.queue.timeout'));
        $this->assertEquals($queue->ringinuse, config('asterisk.queue.ringinuse'));
        $this->assertEquals($queue->setinterfacevar, config('asterisk.queue.setinterfacevar'));
        $this->assertEquals($queue->setqueuevar, config('asterisk.queue.setqueuevar'));
        $this->assertEquals($queue->setqueueentryvar, config('asterisk.queue.setqueueentryvar'));
        $this->assertEquals($queue->announce_frequency, config('asterisk.queue.announce_frequency'));
        $this->assertEquals($queue->announce_to_first_user, config('asterisk.queue.announce_to_first_user'));
        $this->assertEquals($queue->announce_position_limit, config('asterisk.queue.announce_position_limit'));
        $this->assertEquals($queue->periodic_announce_frequency, config('asterisk.queue.periodic_announce_frequency'));
        $this->assertEquals($queue->relative_periodic_announce, config('asterisk.queue.relative_periodic_announce'));
        $this->assertEquals($queue->retry, config('asterisk.queue.retry'));
        $this->assertEquals($queue->wrapuptime, config('asterisk.queue.wrapuptime'));
        $this->assertEquals($queue->autofill, config('asterisk.queue.autofill'));
        $this->assertEquals($queue->autopause, config('asterisk.queue.autopause'));
        $this->assertEquals($queue->autopausebusy, config('asterisk.queue.autopausebusy'));
        $this->assertEquals($queue->autopauseunavail, config('asterisk.queue.autopauseunavail'));
        $this->assertEquals($queue->maxlen, config('asterisk.queue.maxlen'));
        $this->assertEquals($queue->servicelevel, config('asterisk.queue.servicelevel'));
        $this->assertEquals($queue->strategy, config('asterisk.queue.strategy'));
        $this->assertEquals($queue->leavewhenempty, config('asterisk.queue.leavewhenempty'));
        $this->assertEquals($queue->memberdelay, config('asterisk.queue.memberdelay'));
        $this->assertEquals($queue->weight, config('asterisk.queue.weight'));
        $this->assertEquals($queue->timeoutrestart, config('asterisk.queue.timeoutrestart'));
        $this->assertEquals($queue->timeoutpriority, config('asterisk.queue.timeoutpriority'));
        $this->assertEquals($queue->language_default, config('asterisk.queue.language_default'));
    }

    /** @test */
    public function success_update()
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->assertFalse($department->is_insert_asterisk);

        $event = new QueueUpdateOrCreateEvent($department);
        $listener = resolve(QueueUpdateOrInsertListener::class);
        $listener->handle($event);

        $department->refresh();

        $this->assertTrue($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertEquals($queue->name, $department->name);

        $department->update(['name' => $department->name . '_update']);
        $department->refresh();

        $event = new QueueUpdateOrCreateEvent($department);
        $listener = resolve(QueueUpdateOrInsertListener::class);
        $listener->handle($event);

        $this->assertTrue($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertEquals($queue->name, $department->name);
    }

    /** @test */
    public function fail_insert()
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->assertFalse($department->is_insert_asterisk);

        $mockQueueService = Mockery::mock(new QueueService());
        $this->app->instance(QueueService::class, $mockQueueService);
        $mockQueueService->shouldReceive('editOrCreate')
            ->once()
            ->andThrow(Exception::class);

        $event = new QueueUpdateOrCreateEvent($department);
        $listener = resolve(QueueUpdateOrInsertListener::class);
        $listener->handle($event);

        $department->refresh();

        $this->assertFalse($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this-> assertNull($queue);
    }

    /** @test */
    public function fail_update()
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        $this->assertFalse($department->is_insert_asterisk);

        $event = new QueueUpdateOrCreateEvent($department);
        $listener = resolve(QueueUpdateOrInsertListener::class);
        $listener->handle($event);

        $department->refresh();

        $this->assertTrue($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertEquals($queue->name, $department->name);

        $department->update(['name' => $department->name . '_update']);
        $department->refresh();

        $mockQueueService = Mockery::mock(new QueueService());
        $this->app->instance(QueueService::class, $mockQueueService);
        $mockQueueService->shouldReceive('editOrCreate')
            ->once()
            ->andThrow(Exception::class);

        $event = new QueueUpdateOrCreateEvent($department);
        $listener = resolve(QueueUpdateOrInsertListener::class);
        $listener->handle($event);

        $this->assertTrue($department->is_insert_asterisk);

        $queue = $this->queueService->getBy('uuid', $department->guid);

        $this->assertNotEquals($queue->name, $department->name);
    }
}
