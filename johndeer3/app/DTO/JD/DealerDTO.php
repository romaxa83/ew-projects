<?php

namespace App\DTO\JD;

use App\Traits\AssetData;

class DealerDTO
{
    use AssetData;

    public $jdID;
    public $jdjdID;
    public $name;
    public $status;
    public $country;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->jdID = $args['id'];
        $self->jdjdID = $args['jd_id'];
        $self->name = $args["name"];
        $self->country = $args["country"] ?? null;
        $self->status = $args['status'] == 1 ? true : false;

        return $self;
    }
}


