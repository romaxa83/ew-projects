<?php

namespace App\Dto\Warranty\WarrantyInfo;

class WarrantyInfoPackageTranslationDto
{
    private string $title;
    private string $description;
    private string $language;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->title = $args['title'];
        $self->description = $args['description'];
        $self->language = $args['language'];

        return $self;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
