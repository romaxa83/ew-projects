<?php

namespace App\Dto;

trait TranslateDto
{
    /**@var BaseTranslationDto[] $translates */
    private array $translates = [];

    /**
     * @return BaseTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translates;
    }

    protected function setTranslations(array $args): void
    {
        $translations = data_get($args, 'translations');

        if (empty($translations)) {
            return;
        }

        $this->translates = static::$translationDto::byTranslations($translations);
    }
}
