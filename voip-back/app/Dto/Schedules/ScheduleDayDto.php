<?php

namespace App\Dto\Schedules;

final class ScheduleDayDto
{
    public string|int $id;
    public string|null $startWorkTime;
    public string|null $endWorkTime;
    public bool $active;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->id = data_get($args, 'id');
        $self->startWorkTime = data_get($args, 'start_work_time');
        $self->endWorkTime = data_get($args, 'end_work_time');
        $self->active = data_get($args, 'active');

        return $self;
    }
}
