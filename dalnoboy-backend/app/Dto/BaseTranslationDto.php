<?php

namespace App\Dto;

abstract class BaseTranslationDto
{
    private string $language;

    public static function byTranslations(array $translations): array
    {
        $result = [];
        foreach ($translations as $translation) {
            $result[$translation['language']] = static::byTranslate($translation);
        }

        return $result;
    }

    public static function byTranslate(array $translation): static
    {
        $dto = new static();
        $dto->language = $translation['language'];

        return $dto;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
