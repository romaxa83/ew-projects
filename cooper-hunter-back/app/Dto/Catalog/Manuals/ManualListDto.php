<?php

namespace App\Dto\Catalog\Manuals;

class ManualListDto
{
    /**
     * @var array<ManualDto>
     */
    private array $manualsDto = [];

    public static function byArgs(array $args): self
    {
        $self = new self();

        foreach ($args as $arg) {
            $self->manualsDto[] = ManualDto::byArgs($arg);
        }

        return $self;
    }

    /**
     * @return ManualDto[]
     */
    public function getManualsDto(): array
    {
        return $this->manualsDto;
    }
}
