<?php

namespace App\DTO\Permission;

final class RoleTranslationDTO
{
    private string $name;
    private string $lang;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->lang = $args['lang'];

        return $self;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLang()
    {
        return $this->lang;
    }
}

