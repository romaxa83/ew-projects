<?php

namespace App\DTO\Dealership;

class DealershipTranslationDTO
{
    private string $lang;
    private string $name;
    private null|string $text;
    private null|string $address;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->name = $args['name'];
        $self->lang = $args['lang'];
        $self->text = $args['text'];
        $self->address = $args['address'];

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

    public function getText()
    {
        return $this->text;
    }

    public function getAddress()
    {
        return $this->address;
    }
}
