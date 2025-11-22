<?php

namespace App\DTO\Catalog\Region;

use App\DTO\NameTranslationDTO;

final class RegionDTO
{
    private ?int $sort;
    private ?bool $active;
    private array $translations = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->sort = $args['sort'] ?? null;
        $self->active = $args['active'] ?? null;

        foreach ($args['translations'] ?? [] as  $translation){
            $self->translations[] = NameTranslationDTO::byArgs($translation);
        }

        return $self;
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function getActive()
    {
        return $this->active;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}
