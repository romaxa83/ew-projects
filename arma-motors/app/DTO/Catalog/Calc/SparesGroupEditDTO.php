<?php

namespace App\DTO\Catalog\Calc;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

class SparesGroupEditDTO
{
    use AssetData;

    private null|bool $active;
    private null|int $type;
    private null|int $sort;
    private null|int $brandId;
    private array $translations = [];

    private bool $changeSort;
    private bool $changeActive;
    private bool $changeType;
    private bool $changeBrandId;
    private bool $hasTranslations = false;

    private function __construct(array $data)
    {
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeSort = static::checkFieldExist($data, 'sort');
        $this->changeType = static::checkFieldExist($data, 'type');
        $this->changeBrandId = static::checkFieldExist($data, 'brandId');

        foreach ($data['translations'] ?? [] as $translation){
            $this->hasTranslations = true;
            $this->translations[] = SparesGroupTranslationDTO::byArgs($translation);
        }
    }

    public static function byArgs(
        array $args,
    ): self
    {
        $self = new self($args);

        $self->active = $args['active'] ?? null;
        $self->sort = $args['sort'] ?? null;
        $self->type = $args['type'] ?? null;
        $self->brandId = $args['brandId'] ?? null;

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

    public function getType(): null|int
    {
        return $this->type;
    }

    public function getBrandId(): null|int
    {
        return $this->brandId;
    }

    public function changeSort(): bool
    {
        return $this->changeSort;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function changeType(): bool
    {
        return $this->changeType;
    }

    public function changeBrandId(): bool
    {
        return $this->changeBrandId;
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
