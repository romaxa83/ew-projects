<?php

namespace App\Dto;

final class SimpleTranslationDto
{
    private null|string $title = null;
    private string $language;
    private null|string $description = null;
    private null|string $text = null;
    private ?string $seoTitle;
    private ?string $seoDescription;
    private ?string $seoH1;

    private function __construct()
    {
    }

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->title = $args['title'] ?? null;
        $self->language = $args['language'];
        $self->description = $args['description'] ?? null;
        $self->text = $args['text'] ?? null;
        $self->seoTitle = $args['seo_title'] ?? null;
        $self->seoDescription = $args['seo_description'] ?? null;
        $self->seoH1 = $args['seo_h1'] ?? null;

        return $self;
    }

    public function getTitle(): null|string
    {
        return $this->title;
    }

    public function getDescription(): null|string
    {
        return $this->description;
    }

    public function getText(): null|string
    {
        return $this->text;
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
