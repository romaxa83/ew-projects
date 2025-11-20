<?php

namespace App\Dto\Calls;

final class QueueDto
{
    public ?string $name;
    public ?string $serialNumber;
    public ?string $caseID;
    public ?string $comment;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = data_get($args, 'from_name');
        $self->serialNumber = data_get($args, 'serial_number');
        $self->caseID = data_get($args, 'case_id');
        $self->comment = data_get($args, 'comment');

        return $self;
    }
}


