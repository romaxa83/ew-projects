<?php

namespace App\Dto\Catalog\Video;

use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Videos\Group;
use App\Traits\AssertData;

class GroupDto
{
    use AssertData;

    private bool $active;

    /**
     * @var array<SimpleTranslationDto>
     */
    private array $translations = [];

    public static function byArgs(array $args): self
    {
        static::assetField($args, 'translations');

        $self = new self();

        $self->active = $args['active'] ?? Group::DEFAULT_ACTIVE;

        foreach ($args['translations'] ?? [] as $translation) {
            $self->translations[] = SimpleTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return SimpleTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}


