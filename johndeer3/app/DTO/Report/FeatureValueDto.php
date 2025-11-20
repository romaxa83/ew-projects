<?php

namespace App\DTO\Report;

class FeatureValueDto
{
    public $value;
    public $mdID;
    public $mdName;
    public $valueID;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->value = $args['value'] ?? null;
        $self->mdID = $args['id'] ?? null;
        $self->mdName = $args['name'] ?? null;
        $self->valueID = $args['choiceId'] ?? null;

        return $self;
    }
}
