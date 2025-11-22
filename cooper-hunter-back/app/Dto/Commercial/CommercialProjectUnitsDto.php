<?php

namespace App\Dto\Commercial;

class CommercialProjectUnitsDto
{
    /** @var array<CommercialProjectUnitDto> */
    private array $data = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        foreach ($args['data'] ?? [] as $item) {
            $self->data[] = CommercialProjectUnitDto::byArgs($item);
        }

        return $self;
    }

    /**
     * @return CommercialProjectUnitDto[]
     */
    public function getDtos(): array
    {
        return $this->data;
    }
}

