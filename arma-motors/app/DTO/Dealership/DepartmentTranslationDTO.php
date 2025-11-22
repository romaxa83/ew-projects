<?php

namespace App\DTO\Dealership;

class DepartmentTranslationDTO
{
    private string $lang;
    private string $name;
    private null|string $address;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->lang = $args['lang'];
        $self->address = $args['address'] ?? null;

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getAddress(): null|string
    {
        return $this->address;
    }
}

