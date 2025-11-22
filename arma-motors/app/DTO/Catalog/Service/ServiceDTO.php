<?php

namespace App\DTO\Catalog\Service;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

final class ServiceDTO
{
    use AssetData;

    private int $sort = 0;
    private int $time_step = 0;
    private bool $active = true;
    private bool $forGuest = false;
    private null|int $parentId = null;
    private string $alias;
    private null|string $icon = null;
    private array $translations = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'alias');
        self::assetFieldAll($args, 'translations');

        $self = new self();

        $self->sort = $args['sort'] ?? 0;
        $self->time_step = $args['timeStep'] ?? 0;
        $self->active = $args['active'] ?? true;
        $self->forGuest = $args['forGuest'] ?? false;
        $self->alias = $args['alias'];
        $self->parentId = $args['parentId'] ?? null;
        $self->icon = $args['icon'] ?? null;

        foreach ($args['translations'] ?? [] as $translation){
            $self->translations[] = NameTranslationDTO::byArgs($translation);
        }

        return $self;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getTimeStep(): int
    {
        return $this->time_step;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getForGuest(): bool
    {
        return $this->forGuest;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getParentID(): null|int
    {
        return $this->parentId;
    }

    public function getIcon(): null|string
    {
        return $this->icon;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}
