<?php

namespace App\DTO\Order;

use App\Traits\AssetData;
use App\Types\Communication;
use App\ValueObjects\Money;

class OrderInsuranceDTO
{
    use AssetData;

    private int|string $serviceId;
    private int|string $franchiseId;
    private string $communication;
    private null|string $brandId;
    private null|string $modelId;
    private null|string $driverAgeId;
    private null|string $insuranceCompany;
    private null|string $countPayments;
    private null|Money $carCost;

    private null|string $regionId;
    private null|string $cityId;
    private null|string $privilegesId;
    private null|string $transportTypeId;
    private null|string $durationId;
    private bool $useTaxi = false;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'serviceId');
        self::assetFieldAll($args, 'franchiseId');
        self::assetFieldAll($args, 'communication');

        Communication::assert($args['communication']);

        $self = new self();
        // common
        $self->serviceId = $args['serviceId'];
        $self->franchiseId = $args['franchiseId'];
        $self->communication = $args['communication'];
        // casco
        $self->brandId = $args['brandId'] ?? null;
        $self->modelId = $args['modelId'] ?? null;
        $self->driverAgeId = $args['driverAgeId'] ?? null;
        $self->insuranceCompany = $args['insuranceCompany'] ?? null;
        $self->countPayments = $args['countPayments'] ?? null;
        $self->carCost = isset($args['carCost']) ? new Money($args['carCost']) : null;
        // go,dgo
        $self->regionId = $args['regionId'] ?? null;
        $self->cityId = $args['cityId'] ?? null;
        $self->privilegesId = $args['privilegesId'] ?? null;
        $self->transportTypeId = $args['transportTypeId'] ?? null;
        $self->durationId = $args['durationId'] ?? null;
        $self->useTaxi = $args['useTaxi'] ?? false;

        return $self;
    }

    public function getServiceId(): string|int
    {
        return $this->serviceId;
    }

    public function getFranchiseId(): string|int
    {
        return $this->franchiseId;
    }

    public function getCommunication(): string
    {
        return $this->communication;
    }

    public function getBrandId(): null|string
    {
        return $this->brandId;
    }

    public function getModelId(): null|string
    {
        return $this->modelId;
    }

    public function getDriverAgeId(): null|string
    {
        return $this->driverAgeId;
    }

    public function getInsuranceCompany(): null|string
    {
        return $this->insuranceCompany;
    }

    public function getCountPayments(): null|string
    {
        return $this->countPayments;
    }

    public function getCarCost(): null|Money
    {
        return $this->carCost;
    }

    public function getRegionId(): null|string
    {
        return $this->regionId;
    }

    public function getCityId(): null|string
    {
        return $this->cityId;
    }

    public function getPrivilegesId(): null|string
    {
        return $this->privilegesId;
    }

    public function getTransportTypeId(): null|string
    {
        return $this->transportTypeId;
    }

    public function getDurationId(): null|string
    {
        return $this->durationId;
    }

    public function getUseTaxi(): bool
    {
        return $this->useTaxi;
    }
}


