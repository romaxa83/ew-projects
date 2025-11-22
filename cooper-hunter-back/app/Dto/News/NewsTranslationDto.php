<?php

namespace App\Dto\News;

class NewsTranslationDto
{
    private string $title;
    private string $description;
    private string $shortDescription;
    private string $language;
    private ?string $seoTitle;
    private ?string $seoDescription;
    private ?string $seoH1;

    public static function byArgs(array $args): static
    {
        $self = new self();
        $self->title = $args['title'];
        $self->description = $args['description'];
        $self->shortDescription = $args['short_description'];
        $self->language = $args['language'];
        $self->seoTitle = $args['seo_title'] ?? null;
        $self->seoDescription = $args['seo_description'] ?? null;
        $self->seoH1 = $args['seo_h1'] ?? null;

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

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }
}
