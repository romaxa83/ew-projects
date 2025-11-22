<?php

namespace App\DTO\Dealership;

class TimeStepDTO
{
    public int|string $serviceId;
    public int|string $step;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->serviceId = $args['serviceId'];
        $self->step = $args['step'];

        return $self;
    }
}
