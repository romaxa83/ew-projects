<?php

namespace App\Dto\Sliders;

class SliderTranslationDto
{
    private ?string $title;
    private ?string $description;
    private string $language;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->title = !empty($args['title']) ? $args['title'] : null;
        $self->description = !empty($args['description']) ? $args['description'] : null;
        $self->language = $args['language'];

        return $self;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
