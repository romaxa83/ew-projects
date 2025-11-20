<?php

namespace Tests\Builders\Schedules;

use App\Models\Schedules\AdditionsDay;
use App\Models\Schedules\Schedule;
use Carbon\CarbonImmutable;
use Tests\Builders\BaseBuilder;

class AdditionDayBuilder extends BaseBuilder
{
    public function modelClass(): string
    {
        return AdditionsDay::class;
    }

    public function setSchedule(Schedule $model): self
    {
        $this->data['schedule_id'] = $model->id;
        return $this;
    }

    public function setStartAt(CarbonImmutable $value): self
    {
        $this->data['start_at'] = $value;
        return $this;
    }

    public function setEndAt(CarbonImmutable $value): self
    {
        $this->data['end_at'] = $value;
        return $this;
    }
}

