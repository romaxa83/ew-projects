<?php

namespace App\Dto\About\About;

class AboutCompanyTranslationDto
{
    private string $videoLink;
    private string $title;
    private string $description;
    private string $shortDescription;
    private string $language;
    private ?string $seoTitle;
    private ?string $seoDescription;
    private ?string $seoH1;

    private ?string $additionalTitle;
    private ?string $additionalDescription;
    private ?string $additionalVideoLink;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->videoLink = $args['video_link'];
        $dto->title = $args['title'];
        $dto->description = $args['description'];
        $dto->shortDescription = $args['short_description'];
        $dto->language = $args['language'];
        $dto->seoTitle = $args['seo_title'] ?? null;
        $dto->seoDescription = $args['seo_description'] ?? null;
        $dto->seoH1 = $args['seo_h1'] ?? null;

        $dto->additionalTitle = $args['additional_title'] ?? null;
        $dto->additionalDescription = $args['additional_description'] ?? null;
        $dto->additionalVideoLink = $args['additional_video_link'] ?? null;

        return $dto;
    }

    public function getVideoLink(): string
    {
        return $this->videoLink;
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

    public function getAdditionalTitle(): ?string
    {
        return $this->additionalTitle;
    }

    public function getAdditionalDescription(): ?string
    {
        return $this->additionalDescription;
    }

    public function getAdditionalVideoLink(): ?string
    {
        return $this->additionalVideoLink;
    }
}
