<?php

namespace App\DTO;

class SimpleTranslationDto
{
    public $lang;
    public $name;
    public $text;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->lang = $args['lang'];
        $self->name = $args['name'];
        $self->text = $args['text'] ?? null;

        return $self;
    }
}

