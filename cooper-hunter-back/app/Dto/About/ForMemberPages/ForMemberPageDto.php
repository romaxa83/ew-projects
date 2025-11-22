<?php

namespace App\Dto\About\ForMemberPages;

use App\Enums\About\ForMemberPageEnum;

class ForMemberPageDto
{
    private ForMemberPageEnum $for;

    /** @var array<ForMemberPageTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->for = ForMemberPageEnum::fromValue($args['for_member_type']);

        foreach ($args['translations'] as $translation) {
            $dto->translations[] = ForMemberPageTranslationDto::byArgs($translation);
        }

        return $dto;
    }

    public function getFor(): ForMemberPageEnum
    {
        return $this->for;
    }

    /**
     * @return ForMemberPageTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
