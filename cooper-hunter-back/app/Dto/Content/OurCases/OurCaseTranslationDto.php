<?php

namespace App\Dto\Content\OurCases;

class OurCaseTranslationDto
{
    private string $title;
    private string $description;
    private string $language;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->title = $args['title'];
        $dto->description = $args['description'];
        $dto->language = $args['language'];

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
}
