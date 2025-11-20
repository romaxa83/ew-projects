<?php

namespace App\Repositories\Calls;

use App\Enums\Calls\QueueStatus;
use App\Models\Calls\Queue;
use App\Repositories\AbstractRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

final class QueueRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Queue::class;
    }

    public function recsForRemove(): Collection
    {
        return Queue::query()
            ->where('status', QueueStatus::CANCEL())
            ->where('created_at', '<', CarbonImmutable::now()->subHour())
            ->get();
    }
}
