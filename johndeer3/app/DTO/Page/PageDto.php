<?php

namespace App\DTO\Page;

use App\DTO\SimpleTranslationDto;

class PageDto
{
    public $type;
    public $active;
    private $translations = [];

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->type = $args['type'];
        $self->active = $args['active'] ?? true;

        foreach ($args['translations'] ?? [] as  $translation){
            $self->translations[] = SimpleTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getTranslations()
    {
        return $this->translations;
    }
}
