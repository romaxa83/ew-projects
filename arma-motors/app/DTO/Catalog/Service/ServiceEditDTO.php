<?php

namespace App\DTO\Catalog\Service;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

class ServiceEditDTO
{
    use AssetData;

    private null|int $parentId;
    private null|bool $active;
    private null|int $sort;
    private null|int $timeStep;
    private array $translations = [];

    private bool $changeParentId;
    private bool $changeSort;
    private bool $changeTimeStep;
    private bool $changeActive;
    private bool $hasTranslations = false;

    private function __construct(array $data)
    {
        $this->changeParentId = static::checkFieldExist($data, 'parentId');
        $this->changeActive = static::checkFieldExist($data, 'active');
        $this->changeSort = static::checkFieldExist($data, 'sort');
        $this->changeTimeStep = static::checkFieldExist($data, 'timeStep');

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

        $self->parentId = $args['parentId'] ?? null;
        $self->active = $args['active'] ?? null;
        $self->sort = $args['sort'] ?? null;
        $self->timeStep = $args['timeStep'] ?? null;


        return $self;
    }

    public function getParentId(): null|int
    {
        return $this->parentId;
    }

    public function getActive(): null|bool
    {
        return $this->active;
    }

    public function getSort(): null|int
    {
        return $this->sort;
    }

    public function getTimeStep(): null|int
    {
        return $this->timeStep;
    }

    public function changeSort(): bool
    {
        return $this->changeSort;
    }

    public function changeTimeStep(): bool
    {
        return $this->changeTimeStep;
    }

    public function changeActive(): bool
    {
        return $this->changeActive;
    }

    public function changeParentId(): bool
    {
        return $this->changeParentId;
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
