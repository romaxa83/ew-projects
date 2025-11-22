<?php

namespace App\DTO\Catalog\Calc;

use App\Traits\AssetData;

final class SparesGroupDTO
{
    use AssetData;

    private int $sort = 0;
    private bool $active = true;
    private int $type;
    private null|int $brandId;
    private array $translations = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'translations');
        self::assetFieldAll($args, 'type');

        $self = new self();

        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;
        $self->type = $args['type'];
        $self->brandId = $args['brandId'] ?? null;

        foreach ($args['translations'] ?? [] as $translation){
            $self->translations[] = SparesGroupTranslationDTO::byArgs($translation);
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

    public function getType(): int
    {
        return $this->type;
    }

    public function getBrandId(): null|int
    {
        return $this->brandId;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}

