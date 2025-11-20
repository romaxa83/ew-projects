<?php

namespace App\Repositories\Schedules;

use App\Models\Schedules\Schedule;
use App\Repositories\AbstractRepository;

final class ScheduleRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Schedule::class;
    }
}
