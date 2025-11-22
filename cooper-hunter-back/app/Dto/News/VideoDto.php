<?php

namespace App\Dto\News;

use Carbon\Carbon;

class VideoDto
{
    private bool $active;
    private string $slug;

    private Carbon $createdAt;

    /** @var array<VideoTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->active = $args['active'];
        $self->slug = $args['slug'];

        $self->createdAt = isset($args['created_at'])
            ? Carbon::createFromTimestamp($args['created_at'])
            : now();

        foreach ($args['translations'] as $translation) {
            $self->translations[] = VideoTranslationDto::byArgs($translation);
        }

        return $self;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return VideoTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}
