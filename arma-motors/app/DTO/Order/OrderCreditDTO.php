<?php

namespace App\DTO\Order;

use App\Traits\AssetData;
use App\Types\Communication;
use App\Types\UserType;
use App\ValueObjects\Money;

class OrderCreditDTO
{
    use AssetData;

    private int|string $serviceId;
    private string $communication;
    private int|string $brandId;
    private int|string $modelId;

    private Money $carCost;
    private int $firstInstallment;
    private int $typeUser;

    private int|string $durationId;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'serviceId');
        self::assetFieldAll($args, 'brandId');
        self::assetFieldAll($args, 'modelId');
        self::assetFieldAll($args, 'durationId');
        self::assetFieldAll($args, 'communication');
        self::assetFieldAll($args, 'carCost');
        self::assetFieldAll($args, 'firstInstallment');
        self::assetFieldAll($args, 'typeUser');

        Communication::assert($args['communication']);
        UserType::assert($args['typeUser']);

        $self = new self();

        $self->serviceId = $args['serviceId'];
        $self->brandId = $args['brandId'];
        $self->modelId = $args['modelId'];
        $self->carCost = new Money($args['carCost']);
        $self->durationId = $args['durationId'];
        $self->typeUser = $args['typeUser'];
        $self->firstInstallment = $args['firstInstallment'];
        $self->communication = $args['communication'];

        return $self;
    }

    public function getServiceId(): string|int
    {
        return $this->serviceId;
    }

    public function getCommunication(): string
    {
        return $this->communication;
    }

    public function getBrandId(): int|string
    {
        return $this->brandId;
    }

    public function getModelId(): int|string
    {
        return $this->modelId;
    }

    public function getCarCost(): Money
    {
        return $this->carCost;
    }

    public function getDurationId(): int|string
    {
        return $this->durationId;
    }

    public function getFirstInstallment(): int
    {
        return $this->firstInstallment;
    }

    public function getTypeUser(): int
    {
        return $this->typeUser;
    }
}



