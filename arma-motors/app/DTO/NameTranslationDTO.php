<?php

namespace App\DTO;

use Webmozart\Assert\Assert;

final class NameTranslationDTO
{
    private string $name;
    private null|string $text = null;
    private string $lang;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->text = $args['text'] ?? null;
        $self->lang = $args['lang'];

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(): null|string
    {
        return $this->text;
    }

    public function getLang(): string
    {
        return $this->lang;
    }
}

