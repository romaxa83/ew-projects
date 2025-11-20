<?php

namespace App\DTO\JD;

use App\Traits\AssetData;

class ModelDescriptionDTO
{
    use AssetData;

    public $jdID;
    public $name;
    public $egID;
    public $status;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->jdID = $args['id'];
        $self->name = $args["name"];
        $self->egID = $args["equipment_group_id"];
        $self->status = $args['status'] == 1 ? true : false;

        return $self;
    }
}


