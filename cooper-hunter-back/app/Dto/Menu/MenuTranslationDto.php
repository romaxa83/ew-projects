<?php

namespace App\Dto\Menu;

class MenuTranslationDto
{
    private string $title;
    private string $link;
    private string $language;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->title = $args['title'];
        $self->link = $args['link'];
        $self->language = $args['language'];

        return $self;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
