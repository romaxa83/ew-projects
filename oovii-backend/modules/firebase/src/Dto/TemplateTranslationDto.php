<?php

namespace WezomCms\Firebase\Dto;

final class TemplateTranslationDto
{
    public string $locale;
    public string $title;
    public string $text;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->locale = $args['locale'];
        $self->title = $args['title'];
        $self->text = $args['text'];

        return $self;
    }
}
