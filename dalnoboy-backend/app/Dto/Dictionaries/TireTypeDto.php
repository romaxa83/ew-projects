<?php

namespace App\Dto\Dictionaries;

use App\Dto\TranslateDto;

class TireTypeDto
{
    use TranslateDto;

    private static string $translationDto = DictionaryTranslateDto::class;

    private bool $active;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->active = $args['active'];
        $dto->setTranslations($args);

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
