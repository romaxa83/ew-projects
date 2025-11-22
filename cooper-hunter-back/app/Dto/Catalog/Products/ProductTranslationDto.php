<?php

namespace App\Dto\Catalog\Products;

class ProductTranslationDto
{
    private string $language;
    private null|string $description = null;
    private ?string $seoTitle;
    private ?string $seoDescription;
    private ?string $seoH1;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->language = $args['language'];
        $self->description = $args['description'] ?? null;
        $self->seoTitle = $args['seo_title'] ?? null;
        $self->seoDescription = $args['seo_description'] ?? null;
        $self->seoH1 = $args['seo_h1'] ?? null;

        return $self;
    }

    public function getDescription(): null|string
    {
        return $this->description;
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
