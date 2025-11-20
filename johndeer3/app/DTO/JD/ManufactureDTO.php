<?php

namespace App\DTO\JD;

use App\Traits\AssetData;

class ManufactureDTO
{
    use AssetData;

    public $jdID;
    public $name;
    public $status;
    public $position;
    public $isPartnerJD;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->jdID = $args['id'];
        $self->name = $args["name"];
        $self->status = $args['status'] == 1 ? true : false;
        $self->position = $args['position'];
        $self->isPartnerJD = $args['relationship'];

        return $self;
    }
}

