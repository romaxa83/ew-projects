<?php

namespace App\Dto\Schedules;

final class ScheduleDto
{
    /** @var array<ScheduleDayDto> */
    public array $days = [];

    /** @var array<ScheduleAdditionDayDto> */
    public array $additionDays = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        foreach (data_get($args, 'days', []) as $day){
            $self->days[] = ScheduleDayDto::byArgs($day);
        }

        foreach (data_get($args, 'additional_days', []) as $a_day){
            $self->additionDays[] = ScheduleAdditionDayDto::byArgs($a_day);
        }

        return $self;
    }
}
