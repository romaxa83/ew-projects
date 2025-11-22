<?php

namespace App\DTO\Catalog\Calc\Model;

use App\Models\Catalogs\Car\Brand;
use App\Traits\AssetData;

class CalcModelDTO
{
    use AssetData;

    private null|string $brandId = null;
    private null|string $modelId = null;
    private null|string $mileageId = null;
    private null|string $engineVolumeId = null;
    private null|string $driveUnitId = null;
    private null|string $transmissionId = null;
    private null|string $fuelId = null;

    private array $works = [];
    private array $spares = [];

    private function __construct()
    {}

    public static function byArgs(array $args, Brand $brand): self
    {
//        self::assetFieldAll($args, 'brandId');
//        self::assetFieldAll($args, 'modelId');
//        self::assetFieldAll($args, 'mileageId');
//        self::assetFieldAll($args, 'engineVolumeId');
//        self::assetFieldAll($args, 'driveUnitId');
//        self::assetFieldAll($args, 'transmissionId');
//        self::assetFieldAll($args, 'transmissionId');
//        self::assetFieldAll($args, 'works');
//        self::assetFieldAll($args, 'spares');

        $self = new self();
        // Renault
        $self->brandId = $args['brandId'];
        $self->modelId = $args['modelId'];
        $self->mileageId = $args['mileageId'];
        $self->engineVolumeId = $args['engineVolumeId'];
        // + Volvo
        if($brand->isVolvo()){
            $self->fuelId = $args['fuelId'];
        }
        // + Mitsubishi
        if($brand->isMitsubishi()){
            $self->fuelId = $args['fuelId'];
            $self->driveUnitId = $args['driveUnitId'];
            $self->transmissionId = $args['transmissionId'];
        }

        foreach ($args['works'] ?? [] as $work){
            $self->works[] = CalModelWorkDTO::byArgs($work);
        }
        foreach ($args['spares'] ?? [] as $spare){
            $self->spares[] = CalModelSparesDTO::byArgs($spare);
        }

        return $self;
    }

    public function getBrandId(): string|int
    {
        return $this->brandId;
    }

    public function getModelId(): string|int
    {
        return $this->modelId;
    }

    public function getMileageId(): null|string|int
    {
        return $this->mileageId;
    }

    public function getEngineVolumeId(): null|string|int
    {
        return $this->engineVolumeId;
    }

    public function getDriveUnitId(): null|string|int
    {
        return $this->driveUnitId;
    }

    public function getTransmissionId(): null|string|int
    {
        return $this->transmissionId;
    }

    public function getFuelId(): null|string|int
    {
        return $this->fuelId;
    }

    public function getWorks(): array
    {
        return $this->works;
    }

    public function getSpares(): array
    {
        return $this->spares;
    }
}
