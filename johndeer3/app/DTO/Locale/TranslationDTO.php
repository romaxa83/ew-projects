<?php

namespace App\DTO\Locale;

class TranslationDTO
{
    public $model;
    public $entity_type;
    public $entity_id;
    public $text;
    public $lang;
    public $alias;
    public $group;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->model = $args['model'] ?? null;
        $self->entity_id = $args['entity_id'] ?? null;
        $self->entity_type = $args['entity_type'] ?? null;
        $self->text = $args['text'] ?? null;
        $self->lang = $args['lang'] ?? null;
        $self->alias = $args['alias'] ?? null;
        $self->group = $args['group'] ?? null;

        return $self;
    }
}

