<?php

namespace App\Dto\About\About;

class AboutCompanyDto
{
    /** @var array<AboutCompanyTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        foreach ($args['translations'] as $translation) {
            $dto->translations[] = AboutCompanyTranslationDto::byArgs($translation);
        }

        return $dto;
    }

    /**
     * @return AboutCompanyTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
