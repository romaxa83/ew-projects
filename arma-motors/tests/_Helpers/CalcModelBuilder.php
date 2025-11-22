<?php

namespace Tests\_Helpers;

use App\Models\Catalogs\Calc\CalcModel;
use App\Models\Catalogs\Calc\Mileage;
use App\Models\Catalogs\Calc\Spares;
use App\Models\Catalogs\Calc\Work;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\DriveUnit;
use App\Models\Catalogs\Car\EngineVolume;
use App\Models\Catalogs\Car\Fuel;
use App\Models\Catalogs\Car\Model;
use App\Models\Catalogs\Car\Transmission;

class CalcModelBuilder
{
    private $brandId;
    private $modelId;
    private $mileageId;
    private $engineVolumeId;
    private $transmissionId;
    private $driveUnitId;
    private $fuelId;

    private $works = [];
    private $spares = [];

    private $withMileage = true;
    private $withEngineVolume = true;
    private $withTransmission = true;
    private $withDriveUnit = true;
    private $withFuel = true;
    private $withWorks = true;
    private $withSpares = true;

    private $brand;

    public function setBrand(Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getBrand()
    {
        if(null == $this->brand){
            $this->setBrand(Brand::query()->where('name', 'volvo')->first());
        }

        return $this->brand;
    }

    // BrandID
    public function getBrandId()
    {
        if(null == $this->brandId){
            $this->setBrandId($this->getBrandId()->id);
        }

        return $this->brandId;
    }
    public function setBrandId(int $brandId): self
    {
        $this->brandId = $brandId;

        return $this;
    }
    // ModelID
    public function getModelId()
    {
        if(null == $this->modelId){
            $this->setModelId(Model::select('id')->orderBy(\DB::raw('RAND()'))->first()->id);
        }

        return $this->modelId;
    }
    public function setModelId(int $modelId): self
    {
        $this->modelId = $modelId;

        return $this;
    }
    // MileageID
    public function getMileageId()
    {
        if(null == $this->mileageId && $this->withMileage){
            $this->setMileageId(Mileage::select('id')->where('id', 1)->first()->id);
        }

        return $this->mileageId;
    }
    public function setMileageId(int $mileageId): self
    {
        $this->mileageId = $mileageId;

        return $this;
    }
    public function withoutMileage(): self
    {
        $this->withMileage = false;

        return $this;
    }
    // TransmissionID
    public function getTransmissionId()
    {
        if(null == $this->transmissionId  && $this->withTransmission){
            $this->setTransmissionId(Transmission::select('id')->where('id', 1)->first()->id);
        }

        return $this->transmissionId;
    }
    public function setTransmissionId(int $transmissionId): self
    {
        $this->transmissionId = $transmissionId;

        return $this;
    }
    public function withoutTransmission(): self
    {
        $this->withTransmission = false;

        return $this;
    }
    // DriveUnitID
    public function getDriveUnitId()
    {
        if(null == $this->driveUnitId  && $this->withDriveUnit){
            $this->setDriveUnitId(DriveUnit::select('id')->where('id', 1)->first()->id);
        }

        return $this->driveUnitId;
    }
    public function setDriveUnitId(int $driveUnitId): self
    {
        $this->driveUnitId = $driveUnitId;

        return $this;
    }
    public function withoutDriveUnit(): self
    {
        $this->withDriveUnit = false;

        return $this;
    }
    // VolumeID
    public function getVolumeId()
    {
        if(null == $this->engineVolumeId  && $this->withEngineVolume){
            $this->setVolumeId(EngineVolume::select('id')->where('id', 1)->first()->id);
        }

        return $this->engineVolumeId;
    }
    public function setVolumeId(int $volumeId): self
    {
        $this->engineVolumeId = $volumeId;

        return $this;
    }
    public function withoutVolume(): self
    {
        $this->withEngineVolume = false;

        return $this;
    }
    // FuelID
    public function getFuelId()
    {
        if(null == $this->fuelId  && $this->withFuel){
            $this->setFuelId(Fuel::select('id')->where('id', 1)->first()->id);
        }

        return $this->fuelId;
    }
    public function setFuelId(int $fuelId): self
    {
        $this->fuelId = $fuelId;

        return $this;
    }
    public function withoutFuel(): self
    {
        $this->withFuel = false;

        return $this;
    }


    public function setWorks(... $id): self
    {
        $this->works = $id;

        return $this;
    }
    public function withoutWorks(): self
    {
        $this->withWorks = false;

        return $this;
    }

    public function setSpares(... $id): self
    {
        $this->spares = $id;

        return $this;
    }
    public function withoutSpares(): self
    {
        $this->withSpares = false;

        return $this;
    }

    public function create()
    {
        $model = $this->save();

        if(empty($this->works)  && $this->withWorks){
            $this->works = Work::query()->take(3)->pluck('id');
        }
        foreach ($this->works ?? [] as $work){
            $model->works()->attach($work, ['minutes' => random_int(50, 200)]);
        }

        if(empty($this->spares  && $this->withSpares)){
            $this->spares = Spares::query()->take(3)->pluck('id');
        }
        foreach ($this->spares ?? [] as $spare){
            $model->spares()->attach($spare, ['qty' => random_int(5, 20)]);
        }

        $this->clear();

        return $model;
    }

    private function save()
    {
        $data = [];
        $brand = $this->getBrand();
        if($brand->isRenault()){
            $data = [
                'brand_id' => $brand->id,
                'model_id' => $this->getModelId(),
                'mileage_id' => $this->getMileageId(),
                'engine_volume_id' => $this->getVolumeId(),
                'transmission_id' => null,
                'drive_unit_id' => null,
                'fuel_id' => null,
            ];
        }

        if($brand->isVolvo()){

            $data = [
                'brand_id' => $brand->id,
                'model_id' => $this->getModelId(),
                'mileage_id' => $this->getMileageId(),
                'engine_volume_id' => $this->getVolumeId(),
                'transmission_id' => null,
                'drive_unit_id' => null,
                'fuel_id' => $this->getFuelId(),
            ];
        }

        if($brand->isMitsubishi()){
            $data = [
                'brand_id' => $brand->id,
                'model_id' => $this->getModelId(),
                'mileage_id' => $this->getMileageId(),
                'engine_volume_id' => $this->getVolumeId(),
                'transmission_id' => $this->getTransmissionId(),
                'drive_unit_id' => $this->getDriveUnitId(),
                'fuel_id' => $this->getFuelId(),
            ];
        }

        if(empty($data)){
            throw new \Exception('Empty data');
        }

        return CalcModel::factory()->new($data)->create();
    }
    private function clear()
    {
        $this->brandId = null;
        $this->brand = null;
        $this->modelId = null;
        $this->mileageId = null;
        $this->engineVolumeId = null;
        $this->transmissionId = null;
        $this->driveUnitId = null;
        $this->fuelId = null;
        $this->works = [];
        $this->spares = [];

        $this->withMileage = true;
        $this->withEngineVolume = true;
        $this->withTransmission = true;
        $this->withDriveUnit = true;
        $this->withFuel = true;
        $this->withWorks = true;
        $this->withSpares = true;
    }
}

