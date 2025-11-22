<?php

namespace App\DTO\Catalog\Service;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

final class DurationDTO
{
    use AssetData;

    private int $sort = 0;
    private bool $active = true;
    private array $translations = [];
    private array $serviceIds = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'translations');

        $self = new self();

        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;
        $self->serviceIds = $args['serviceIds'] ?? [];

        foreach ($args['translations'] ?? [] as $translation){
            $self->translations[] = NameTranslationDTO::byArgs($translation);
        }

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

    public function getTranslations()
    {
        return $this->translations;
    }

    public function getServiceIds(): array
    {
        return $this->serviceIds;
    }

    public function emptyServiceIds(): bool
    {
        return empty($this->serviceIds);
    }
}
