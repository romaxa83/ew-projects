<?php

namespace App\DTO\User;

use App\Models\User\Car;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;

class CarDTO
{
    private $brandId;
    private $modelId;
    private CarNumber $number;
    private CarVin $vin;
    private string $year;
    private bool $isPersonal = false;
    private int $status = Car::DRAFT;
    private bool $isAddToApp = false;
    private bool $isVerify = false;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->brandId = $args['brandId'];
        $self->modelId = $args['modelId'];
        $self->number = new CarNumber($args['number']);
        $self->vin = new CarVin($args['vin']);
        $self->year = $args['year'];
        $self->isPersonal = $args['isPersonal'];

        return $self;
    }

    public function getBrandId()
    {
        return $this->brandId;
    }

    public function getModelId()
    {
        return $this->modelId;
    }

    public function getNumber(): CarNumber
    {
        return $this->number;
    }

    public function getVin(): CarVin
    {
        return $this->vin;
    }

    public function getYear(): string
    {
        return $this->year;
    }

    public function getIsPersonal(): bool
    {
        return $this->isPersonal;
    }

    // status
    public function getStatus(): int
    {
        return $this->status;
    }
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    // isAddToApp
    public function getIsAddToApp(): bool
    {
        return $this->isAddToApp;
    }
    public function setIsAddToApp(): self
    {
        $this->isAddToApp = true;

        return $this;
    }

    // verify
    public function getIsVerify(): bool
    {
        return $this->isVerify;
    }
    public function setIsVerify(): self
    {
        $this->isVerify = true;

        return $this;
    }
}
