<?php

namespace Tests\_Helpers;

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\Car;
use App\Models\User\Confidant;
use App\Models\User\OrderCar\OrderCar;
use App\Models\User\User;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;
use Carbon\Carbon;

class CarBuilder
{
    private $brandId;
    private $modelId;
    private $userId;
    private $number;
    private $vin;
    private $year;
    private $status;
    private bool $isVerify = true;
    private bool $isPersonal = false;
    private bool $isBuy = false;
    private bool $isAddToApp = false;
    private bool $selected = false;
    private bool $softDeleted = false;

    private bool $withConfidant = false;
    private bool $withoutVin = false;

    private bool $withOrder = false;

    private bool $inGarage = false;
    private null|string $uuid = null;

    // BrandID
    public function getBrandId()
    {
        if(null == $this->brandId){
            $this->setBrandId(Brand::select('id')->orderBy(\DB::raw('RAND()'))->first()->id);
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
    // Number
    public function getNumber()
    {
        if(null == $this->number){
            $this->setNumber(\Str::random(8));
        }

        return $this->number;
    }
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }
    // Vin
    public function getVin()
    {
        if(null == $this->vin){
            $this->setVin(\Str::random(8));
        }

        return $this->vin;
    }
    public function setVin(string $vin): self
    {
        $this->vin = $vin;

        return $this;
    }
    // Year
    public function getYear()
    {
        if(null == $this->year){
            $this->setYear('2012');
        }

        return $this->year;
    }
    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }
    // UserID
    public function getUserId()
    {
        if(null == $this->userId){
            $user = User::factory()->create();
            $this->setUserId($user->id);
        }
        return $this->userId;
    }
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
    // Selected
    public function selected(): self
    {
        $this->selected = true;

        return $this;
    }
    public function getSelected(): bool
    {
        return $this->selected;
    }
    // Verify
    public function notVerify(): self
    {
        $this->isVerify = false;

        return $this;
    }
    public function getIsVerify(): bool
    {
        return $this->isVerify;
    }
    // Status
    public function getStatus()
    {
        if(null == $this->status){
            $this->setStatus(Car::DRAFT);
        }

        return $this->status;
    }
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    // Garage
    public function getInGarage()
    {
        return $this->inGarage;
    }
    public function setInGarage(bool $status): self
    {
        $this->inGarage = $status;

        return $this;
    }

    // SoftDeleted
    public function softDeleted(): self
    {
        $this->softDeleted = true;

        return $this;
    }

    public function withConfidant(): self
    {
        $this->withConfidant = true;

        return $this;
    }
    public function withoutVin(): self
    {
        $this->withoutVin = true;

        return $this;
    }

    public function withOrder(): self
    {
        $this->withOrder = true;

        return $this;
    }

    // Uuid
    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }
    public function getUuid()
    {
        return $this->uuid;
    }

    public function create()
    {
        $car = $this->save();

        if($this->withConfidant){
            Confidant::factory()->create(['car_id' => $car->id]);
        }

        $this->clear();

        return $car;
    }

    private function save()
    {
        $data = [
            'brand_id' => $this->getBrandId(),
            'model_id' => $this->getModelId(),
            'user_id' => $this->getUserId(),
            'selected' => $this->getSelected(),
            'is_verify' => $this->getIsVerify(),
            'inner_status' => $this->getStatus(),
            'number' => new CarNumber($this->getNumber()),
            'vin' => new CarVin($this->getVin()),
            'year' => $this->getYear(),
            'in_garage' => $this->getInGarage(),
            'uuid' => $this->getUuid(),
        ];

        if($this->withoutVin){
            $data['vin'] = null;
        }

        if($this->softDeleted){
            $data['deleted_at'] = Carbon::now();
        }

        $car = Car::factory()->new($data)->create();

        if($this->withOrder){
            OrderCar::factory()->new([
                'car_id' => $car->id
            ])->create();
        }

        return $car;
    }
    private function clear()
    {
        $this->brandId = null;
        $this->modelId = null;
        $this->selected = false;
        $this->withConfidant = false;
        $this->isVerify = true;
        $this->status = null;
        $this->vin = null;
        $this->number = null;
        $this->inGarage = false;
        $this->uuid = null;
        $this->withOrder = false;
    }
}
