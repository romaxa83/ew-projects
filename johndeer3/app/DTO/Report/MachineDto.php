<?php

namespace App\DTO\Report;

class MachineDto
{
    public $manufacturerID;
    public $egID;
    public $mdID;
    public $trailedEquipmentType;
    public $trailerModel;
    public $headerBrandID;
    public $headerModelID;
    public $serialNumberHeader;
    public $machineSerialNumber;
    public $subMachineSerialNumber;
    public $subEgID;
    public $subMdID;
    public $subManufacturerID;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->manufacturerID = $args['manufacturer_id'] ?? null;
        $self->egID = $args['equipment_group_id'] ?? null;
        $self->mdID = $args['model_description_id'] ?? null;
        $self->trailedEquipmentType = $args['trailed_equipment_type'] ?? null;
        $self->trailerModel = $args['trailer_model'] ?? null;
        $self->headerBrandID = $args['header_brand_id'] ?? null;
        $self->headerModelID = $args['header_model_id'] ?? null;
        $self->serialNumberHeader = $args['serial_number_header'] ?? null;
        $self->machineSerialNumber = $args['machine_serial_number'] ?? null;
        $self->subMachineSerialNumber = $args['sub_machine_serial_number'] ?? null;
        $self->subEgID = $args['sub_equipment_group_id'] ?? null;
        $self->subMdID = $args['sub_model_description_id'] ?? null;
        $self->subManufacturerID = $args['sub_manufacturer_id'] ?? null;

        return $self;
    }
}
