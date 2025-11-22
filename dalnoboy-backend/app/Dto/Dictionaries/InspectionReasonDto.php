<?php

namespace App\Dto\Dictionaries;

use App\Dto\TranslateDto;

class InspectionReasonDto
{
    use TranslateDto;

    private static string $translationDto = DictionaryTranslateDto::class;

    private bool $active;
    private bool $needDescription;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->active = $args['active'];
        $dto->needDescription = $args['need_description'];
        $dto->setTranslations($args);

        return $dto;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isNeedDescription(): bool
    {
        return $this->needDescription;
    }
}
