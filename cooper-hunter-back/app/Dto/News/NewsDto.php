<?php

namespace App\Dto\News;

use Carbon\Carbon;

class NewsDto
{
    private int $tagId;
    private bool $active;
    private string $slug;

    private Carbon $createdAt;

    /** @var array<NewsTranslationDto> */
    private array $translations;

    public static function byArgs(array $args): static
    {
        $self = new self();
        $self->tagId = $args['tag_id'];
        $self->active = $args['active'];
        $self->slug = $args['slug'];

        $self->createdAt = isset($args['created_at'])
            ? Carbon::createFromTimestamp($args['created_at'])
            : now();

        foreach ($args['translations'] as $t) {
            $self->translations[] = NewsTranslationDto::byArgs($t);
        }

        return $self;
    }

    public function getTagId(): int
    {
        return $this->tagId;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @return NewsTranslationDto[]
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
