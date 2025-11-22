<?php

namespace App\Dto\Catalog\Video;

use App\Dto\SimpleTranslationDto;
use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\Models\Catalog\Videos\VideoLink;
use App\Traits\AssertData;

class LinkDto
{
    use AssertData;

    private VideoLinkTypeEnum $linkType;

    private bool $active;
    private string $link;
    private int $groupId;

    /**
     * @var array<SimpleTranslationDto>
     */
    private array $translations = [];

    public static function byArgs(array $args): self
    {
        static::assetField($args, 'link');
        static::assetField($args, 'group_id');
        static::assetField($args, 'translations');

        $self = new self();

        $self->linkType = VideoLinkTypeEnum::fromValue($args['link_type']);

        $self->active = $args['active'] ?? VideoLink::DEFAULT_ACTIVE;
        $self->link = $args['link'];
        $self->groupId = $args['group_id'];

        foreach ($args['translations'] ?? [] as $translation) {
            $self->translations[] = SimpleTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getLinkType(): VideoLinkTypeEnum
    {
        return $this->linkType;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return SimpleTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}

