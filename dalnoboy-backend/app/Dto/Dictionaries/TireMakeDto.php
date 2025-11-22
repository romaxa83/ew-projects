<?php

namespace App\Dto\Dictionaries;

class TireMakeDto
{
    private bool $active;
    private string $title;
    private bool $isModerated;
    private bool $isOffline;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->active = $args['active'];
        $dto->title = $args['title'];
        $dto->isModerated = $args['is_moderated'];
        $dto->isOffline = $args['is_offline'];

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isModerated(): bool
    {
        return $this->isModerated;
    }

    public function isOffline(): bool
    {
        return $this->isOffline;
    }
}
