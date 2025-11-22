<?php

namespace App\Dto\OneC;

class ImportStatsDto
{
    private int $total;
    private int $new;
    private int $exists;
    private int $updated;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->total = $args['total'];
        $self->new = $args['new'];
        $self->exists = $args['exists'];
        $self->updated = $args['updated'];

        return $self;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getNew(): int
    {
        return $this->new;
    }

    public function getExists(): int
    {
        return $this->exists;
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }
}
