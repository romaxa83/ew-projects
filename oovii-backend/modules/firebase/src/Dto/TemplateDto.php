<?php

namespace WezomCms\Firebase\Dto;

final class TemplateDto
{
    public string $type;
    public bool $active;
    public array $vars;
    public array $translations = [];

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->type = $args['type'];
        $self->active = $args['active'] ?? true;
        $self->vars = $args['vars'] ?? [];

        foreach ($args['translations'] ?? [] as $translation){
            $self->translations[] = TemplateTranslationDto::byArgs($translation);
        }

        return $self;
    }
}
