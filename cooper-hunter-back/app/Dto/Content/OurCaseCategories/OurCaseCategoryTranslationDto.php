<?php

namespace App\Dto\Content\OurCaseCategories;

class OurCaseCategoryTranslationDto
{
    private string $title;
    private string $description;
    private string $language;
    private ?string $seoTitle;
    private ?string $seoDescription;
    private ?string $seoH1;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->title = $args['title'];
        $dto->description = $args['description'];
        $dto->language = $args['language'];

        $dto->seoTitle = $args['seo_title'] ?? null;
        $dto->seoDescription = $args['seo_description'] ?? null;
        $dto->seoH1 = $args['seo_h1'] ?? null;

        return $dto;
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
