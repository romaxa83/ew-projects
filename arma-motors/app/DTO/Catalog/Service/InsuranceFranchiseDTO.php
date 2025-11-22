<?php

namespace App\DTO\Catalog\Service;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

final class InsuranceFranchiseDTO
{
    use AssetData;

    private int $sort = 0;
    private bool $active = true;
    private string $name;
    private array $insuranceIds = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        static::assetFieldExist($args, 'name');

        $self = new self();

        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;
        $self->name = $args['name'];
        $self->insuranceIds = $args['insuranceIds'] ?? [];

        return $self;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInsuranceIds(): array
    {
        return $this->insuranceIds;
    }

    public function emptyInsuranceIds(): bool
    {
        return empty($this->insuranceIds);
    }
}
