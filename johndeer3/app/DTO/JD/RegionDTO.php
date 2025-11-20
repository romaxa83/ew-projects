<?php

namespace App\DTO\JD;

use App\Traits\AssetData;

class RegionDTO
{
    use AssetData;

    public $jdID;
    public $name;
    public $status;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->jdID = $args['id'];
        $self->name = $args["name"];
        $self->status = $args['status'] == 1 ? true : false;

        return $self;
    }
}
