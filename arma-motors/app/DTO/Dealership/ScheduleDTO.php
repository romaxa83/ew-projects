<?php

namespace App\DTO\Dealership;

class ScheduleDTO
{
    private int $day;
    private null|int $from;
    private null|int $to;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->day = $args['day'];
        $self->from = $args['from'];
        $self->to = $args['to'];

        return $self;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function getFrom(): null|int
    {
        return $this->from;
    }

    public function getTo(): null|int
    {
        return $this->to;
    }
}


