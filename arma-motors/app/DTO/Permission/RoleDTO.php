<?php

namespace App\DTO\Permission;

use App\DTO\NameTranslationDTO;

final class RoleDTO
{
    private ?string $name;
    private array $translations = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'] ?? null;

        foreach ($args['translations'] ?? [] as  $translation){
            $self->translations[] = NameTranslationDTO::byArgs($translation);
        }

        return $self;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}
