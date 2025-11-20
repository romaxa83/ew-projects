<?php

namespace App\Dto\Music;

final class MusicDto
{
    public int $interval;
    public bool $active;
    public int $departmentId;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->interval = data_get($args, 'interval');
        $self->active = data_get($args, 'active', true);
        $self->departmentId = data_get($args, 'department_id');

        return $self;
    }
}

