<?php

namespace App\DTO\Catalog\Service;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

class DurationEditDTO
{
    use AssetData;

    private null|bool $active;
    private null|int $sort;
    private array $translations = [];
    private array $serviceIds = [];

    private bool $changeSort;
    private bool $changeActive;
    private bool $hasTranslations = false;

    private function __construct(array $data)
    {
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeSort = static::checkFieldExist($data, 'sort');

        foreach ($data['translations'] ?? [] as $translation){
            $this->hasTranslations = true;
            $this->translations[] = NameTranslationDTO::byArgs($translation);
        }
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->active = $args['active'] ?? null;
        $self->sort = $args['sort'] ?? null;
        $self->serviceIds = $args['serviceIds'] ?? [];

        return $self;
    }

    public function getActive(): null|bool
    {
        return $this->active;
    }

    public function getSort(): null|int
    {
        return $this->sort;
    }

    public function getServiceIds(): array
    {
        return $this->serviceIds;
    }

    public function emptyServiceIds(): bool
    {
        return empty($this->serviceIds);
    }

    public function changeSort(): bool
    {
        return $this->changeSort;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function hasTranslations(): bool
    {
        return $this->hasTranslations;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
