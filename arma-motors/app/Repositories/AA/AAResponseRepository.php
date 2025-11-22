<?php

namespace App\Repositories\AA;

use App\Models\AA\AAResponse;
use App\Repositories\AbstractRepository;
use Carbon\CarbonImmutable;

class AAResponseRepository extends AbstractRepository
{
    public function query()
    {
        return AAResponse::query();
    }

    public function getForRemove($days)
    {
        $now = CarbonImmutable::now()->subDays($days);
        return $this->query()
            ->where('created_at', '<', $now)
            ->get();
    }
}

