<?php

namespace App\Dto\Dictionaries;

use App\Dto\BaseTranslationDto;

class DictionaryTranslateDto extends BaseTranslationDto
{
    private string $title;

    public static function byTranslate(array $translation): static
    {
        $dto = parent::byTranslate($translation);

        $dto->title = $translation['title'];

        return $dto;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
