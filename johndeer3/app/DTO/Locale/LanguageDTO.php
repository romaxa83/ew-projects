<?php

namespace App\DTO\Locale;

class LanguageDTO
{
    public $name;
    public $native;
    public $slug;
    public $locale;
    public $default;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->native = $args['native'] ?? null;
        $self->slug = $args['slug'];
        $self->locale = $args['locale'];
        $self->default = $args['default'] ?? false;

        return $self;
    }
}
