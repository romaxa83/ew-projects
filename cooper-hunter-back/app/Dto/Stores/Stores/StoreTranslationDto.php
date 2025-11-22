<?php

namespace App\Dto\Stores\Stores;

use App\Dto\BaseTranslationDto;

class StoreTranslationDto extends BaseTranslationDto
{
    private string $title;
    private string $language;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->title = $args['title'];
        $self->language = $args['language'];

        return $self;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
