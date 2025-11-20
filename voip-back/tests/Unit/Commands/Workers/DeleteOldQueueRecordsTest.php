<?php

namespace Tests\Unit\Commands\Workers;

use App\Enums\Calls\QueueStatus;
use App\Models\Calls\Queue;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\QueueBuilder;
use Tests\TestCase;

class DeleteOldQueueRecordsTest extends TestCase
{
    use DatabaseTransactions;

    protected QueueBuilder $queueBuilder;
    protected function setUp(): void
    {
        parent::setUp();

        $this->queueBuilder = resolve( QueueBuilder::class);
    }

    /** @test */
    public function remove_old(): void
    {
        $q_1 = $this->queueBuilder->setStatus(QueueStatus::CANCEL())
            ->setData([
                'created_at' => CarbonImmutable::now()->subHours(2)
            ])->create();
        $q_2 = $this->queueBuilder->setStatus(QueueStatus::CANCEL())->create();

        $q_1_id = $q_1->id;
        $q_2_id = $q_2->id;

        $this->artisan('workers:remove_old_queue_recs');

        $this->assertNull(Queue::find($q_1_id));
        $this->assertNotNull(Queue::find($q_2_id));
    }
}

