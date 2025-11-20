<?php

namespace Tests\Unit\IPTelephony\Services\Storage\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\TestCase;

class QueueServiceTest extends TestCase
{
    private QueueService $service;
    protected DepartmentBuilder $departmentBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(QueueService::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
    }

    /** @test */
    public function prepare_data_for_insert(): void
    {
        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        $data = $this->service->prepareDataForInsert($model);

        $this->assertEquals(data_get($data, 'name'), $model->name);
        $this->assertEquals(data_get($data, 'uuid'), $model->guid);
        $this->assertEquals(data_get($data, 'musiconhold'), config('asterisk.queue.musiconhold'));
        $this->assertEquals(data_get($data, 'timeout'), config('asterisk.queue.timeout'));
        $this->assertEquals(data_get($data, 'queue_timeout'), config('asterisk.queue.queue_timeout'));
        $this->assertEquals(data_get($data, 'ringinuse'), config('asterisk.queue.ringinuse'));
        $this->assertEquals(data_get($data, 'setinterfacevar'), config('asterisk.queue.setinterfacevar'));
        $this->assertEquals(data_get($data, 'setqueuevar'), config('asterisk.queue.setqueuevar'));
        $this->assertEquals(data_get($data, 'setqueueentryvar'), config('asterisk.queue.setqueueentryvar'));
        $this->assertEquals(data_get($data, 'announce_frequency'), config('asterisk.queue.announce_frequency'));
        $this->assertEquals(data_get($data, 'announce_to_first_user'), config('asterisk.queue.announce_to_first_user'));
        $this->assertEquals(data_get($data, 'announce_position_limit'), config('asterisk.queue.announce_position_limit'));
        $this->assertEquals(data_get($data, 'periodic_announce_frequency'), config('asterisk.queue.periodic_announce_frequency'));
        $this->assertEquals(data_get($data, 'relative_periodic_announce'), config('asterisk.queue.relative_periodic_announce'));
        $this->assertEquals(data_get($data, 'retry'), config('asterisk.queue.retry'));
        $this->assertEquals(data_get($data, 'wrapuptime'), config('asterisk.queue.wrapuptime'));
        $this->assertEquals(data_get($data, 'autofill'), config('asterisk.queue.autofill'));
        $this->assertEquals(data_get($data, 'autopause'), config('asterisk.queue.autopause'));
        $this->assertEquals(data_get($data, 'autopausebusy'), config('asterisk.queue.autopausebusy'));
        $this->assertEquals(data_get($data, 'autopauseunavail'), config('asterisk.queue.autopauseunavail'));
        $this->assertEquals(data_get($data, 'maxlen'), config('asterisk.queue.maxlen'));
        $this->assertEquals(data_get($data, 'servicelevel'), config('asterisk.queue.servicelevel'));
        $this->assertEquals(data_get($data, 'strategy'), config('asterisk.queue.strategy'));
        $this->assertEquals(data_get($data, 'leavewhenempty'), config('asterisk.queue.leavewhenempty'));
        $this->assertEquals(data_get($data, 'reportholdtime'), config('asterisk.queue.reportholdtime'));
        $this->assertEquals(data_get($data, 'memberdelay'), config('asterisk.queue.memberdelay'));
        $this->assertEquals(data_get($data, 'weight'), config('asterisk.queue.weight'));
        $this->assertEquals(data_get($data, 'timeoutrestart'), config('asterisk.queue.timeoutrestart'));
        $this->assertEquals(data_get($data, 'timeoutpriority'), config('asterisk.queue.timeoutpriority'));
        $this->assertEquals(data_get($data, 'language_default'), config('asterisk.queue.language_default'));
    }
}
