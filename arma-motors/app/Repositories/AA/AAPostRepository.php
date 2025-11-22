<?php

namespace App\Repositories\AA;

use App\Models\AA\AAPostSchedule;
use App\Repositories\AbstractRepository;
use Carbon\CarbonImmutable;

class AAPostRepository extends AbstractRepository
{
    public function query()
    {
        return AAPostSchedule::query();
    }

    public function getForRemove()
    {
        $now = CarbonImmutable::now();
        return $this->query()
            ->where('date', '<', $now)
            ->get();
    }
}
