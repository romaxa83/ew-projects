<?php

namespace App\Dto\Dictionaries;

use GraphQL\Type\Definition\IDType;

class TireSizeDto
{
    private bool $active;
    private IDType|int|null $tireWidthId;
    private IDType|int $tireHeightId;
    private IDType|int $tireDiameterId;
    private bool $isModerated;
    private bool $isOffline;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->active = $args['active'];
        $dto->tireWidthId = $args['tire_width_id'] ?? null;
        $dto->tireHeightId = $args['tire_height_id'];
        $dto->tireDiameterId = $args['tire_diameter_id'];
        $dto->isModerated = $args['is_moderated'];
        $dto->isOffline = $args['is_offline'];

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getTireWidthId(): IDType|int|null
    {
        return $this->tireWidthId;
    }

    public function getTireHeightId(): IDType|int
    {
        return $this->tireHeightId;
    }

    public function getTireDiameterId(): IDType|int
    {
        return $this->tireDiameterId;
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
