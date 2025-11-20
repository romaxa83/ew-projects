<?php

namespace App\DTO\Report;

class FeatureDto
{
    public $ID;
    public $isSub;
    public $values = [];

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->ID = $args['id'];
        $self->isSub = $args['is_sub'] ?? false;

        foreach ($args['group'] ?? [] as $value){
            $self->values[] = FeatureValueDto::byArgs($value);
        }

        return $self;
    }
}


