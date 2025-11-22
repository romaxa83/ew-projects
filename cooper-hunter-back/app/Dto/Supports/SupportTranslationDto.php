<?php

namespace App\Dto\Supports;

class SupportTranslationDto
{
    private string $description;
    private string $shortDescription;
    private string $workingTime;
    private string $videoLink;
    private string $language;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->description = $args['description'];
        $self->shortDescription = $args['short_description'];
        $self->workingTime = $args['working_time'];
        $self->videoLink = $args['video_link'];
        $self->language = $args['language'];

        return $self;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function getWorkingTime(): string
    {
        return $this->workingTime;
    }

    public function getVideoLink(): string
    {
        return $this->videoLink;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
