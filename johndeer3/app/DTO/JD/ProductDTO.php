<?php

namespace App\DTO\JD;

use App\Traits\AssetData;

class ProductDTO
{
    use AssetData;

    public $jdID;
    public $status;
    public $sizeName;
    public $type;
    public $jdModelDescriptionID;
    public $jdEquipmentGroupID;
    public $jdManufactureID;
    public $jdSizeParameterID;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->jdID = $args['id'];
        $self->sizeName = $args['size_name'];
        $self->type = $args['type'];
        $self->jdModelDescriptionID = $args['model_description_id'];
        $self->jdEquipmentGroupID = $args['equipment_group_id'];
        $self->jdManufactureID = $args['manufacture_id'];
        $self->jdSizeParameterID = $args['size_parameter_id'];
        $self->status = $args['status'] == 1 ? true : false;

        return $self;
    }
}
