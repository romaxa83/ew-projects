<?php

namespace App\Dto\Dictionaries;

use GraphQL\Type\Definition\IDType;

class TireSpecificationDto
{
    private bool $active;
    private IDType|int $makeId;
    private IDType|int $modelId;
    private IDType|int $typeId;
    private IDType|int $sizeId;
    private float $ngp;
    private bool $isModerated;
    private bool $isOffline;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->active = $args['active'];
        $dto->makeId = $args['make_id'];
        $dto->modelId = $args['model_id'];
        $dto->typeId = $args['type_id'];
        $dto->sizeId = $args['size_id'];
        $dto->ngp = $args['ngp'];
        $dto->isModerated = $args['is_moderated'];
        $dto->isOffline = $args['is_offline'];

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getMakeId(): int|IDType
    {
        return $this->makeId;
    }

    public function getModelId(): int|IDType
    {
        return $this->modelId;
    }

    public function getTypeId(): int|IDType
    {
        return $this->typeId;
    }

    public function getSizeId(): int|IDType
    {
        return $this->sizeId;
    }

    public function getNgp(): float
    {
        return $this->ngp;
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
