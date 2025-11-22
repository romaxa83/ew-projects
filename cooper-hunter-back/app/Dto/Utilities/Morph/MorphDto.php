<?php

namespace App\Dto\Utilities\Morph;

class MorphDto
{
    public int $id;
    public string $type;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->id = data_get($args, 'id');
        $dto->type = data_get($args, 'type');

        return $dto;
    }
}
