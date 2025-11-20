<?php

namespace App\Dto\Schedules;

final class ScheduleAdditionDayDto
{
    public string|int $id;
    public string $startAt;
    public string|null $endAt;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->startAt = data_get($args, 'start_at');
        $self->endAt = data_get($args, 'end_at');

        return $self;
    }
}

