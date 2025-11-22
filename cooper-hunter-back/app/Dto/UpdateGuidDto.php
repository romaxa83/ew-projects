<?php

namespace App\Dto;

class UpdateGuidDto
{
    private int $id;
    private string $guid;

    public static function byArgs(array $args): static
    {
        $instance = new static();

        $instance->id = $args['id'];
        $instance->guid = $args['guid'];

        return $instance;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }
}
