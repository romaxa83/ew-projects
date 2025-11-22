<?php

namespace App\DTO\Catalog\Car;

use App\DTO\NameTranslationDTO;
use App\Traits\AssetData;

final class TransmissionDTO
{
    use AssetData;

    private int $sort = 0;
    private bool $active = true;
    private array $translations = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'translations');

        $self = new self();

        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;

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
}

