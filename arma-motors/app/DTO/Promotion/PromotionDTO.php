<?php

namespace App\DTO\Promotion;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

final class PromotionDTO
{
    use AssetData;

    private int $sort = 0;
    private bool $active = true;
    private string $type;
    private null|string $link;
    private int|string $departmentId;
    private int|string $startAt;
    private int|string $finishAt;
    private array $translations = [];
    private array $userIds = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'type');
        self::assetFieldAll($args, 'departmentId');
        self::assetFieldAll($args, 'startAt');
        self::assetFieldAll($args, 'finishAt');
        self::assetFieldAll($args, 'translations');

        $self = new self();

        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;
        $self->type = $args['type'];
        $self->departmentId = $args['departmentId'];
        $self->link = $args['link'] ?? null;
        $self->startAt = $args['startAt'];
        $self->finishAt = $args['finishAt'];
        $self->userIds = $args['userIds'] ?? [];

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

    public function getType(): string
    {
        return $this->type;
    }

    public function getLink(): null|string
    {
        return $this->link;
    }

    public function getDepartmentId(): int|string
    {
        return $this->departmentId;
    }

    public function getStartAt(): int|string
    {
        return $this->startAt;
    }

    public function getFinishAt(): int|string
    {
        return $this->finishAt;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }

    public function emptyUserIds(): bool
    {
        return empty($this->userIds);
    }
}

