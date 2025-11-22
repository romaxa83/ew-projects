<?php

namespace App\Dto\Warranty\WarrantyInfo;

class WarrantyInfoTranslationDto
{
    private string $description;
    private string $notice;
    private string $language;
    private ?string $seoTitle;
    private ?string $seoDescription;
    private ?string $seoH1;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->description = $args['description'];
        $self->notice = $args['notice'];
        $self->language = $args['language'];
        $self->seoTitle = $args['seo_title'] ?? null;
        $self->seoDescription = $args['seo_description'] ?? null;
        $self->seoH1 = $args['seo_h1'] ?? null;

        return $self;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getNotice(): string
    {
        return $this->notice;
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
