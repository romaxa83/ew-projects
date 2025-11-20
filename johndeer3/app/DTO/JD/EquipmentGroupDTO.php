<?php

namespace App\DTO\JD;

use App\Traits\AssetData;

class EquipmentGroupDTO
{
    use AssetData;

    public $jdID;
    public $name;
    public $status;
    public $forStatistic;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->jdID = $args['id'];
        $self->name = $args["name"];
        $self->status = $args['status'] == 1 ? true : false;
        $self->forStatistic = $args['for_statistic'] ?? false;

        return $self;
    }
}

