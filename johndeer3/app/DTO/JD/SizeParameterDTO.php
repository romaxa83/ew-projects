<?php

namespace App\DTO\JD;

use App\Traits\AssetData;

class SizeParameterDTO
{
    use AssetData;

    public $jdID;
    public $status;
    public $name;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        self::assetFieldInArray($args, 'id');
        self::assetFieldInArray($args, 'name');

        $self = new self();

        $self->jdID = $args['id'];
        $self->name = $args['name'];
        $self->status = $args['status'] == 1 ? true : false;

        return $self;
    }
}
