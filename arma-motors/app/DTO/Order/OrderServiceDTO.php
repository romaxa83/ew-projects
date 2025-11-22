<?php

namespace App\DTO\Order;

use App\Traits\AssetData;
use App\Types\Communication;

class OrderServiceDTO
{
    use AssetData;

    public $uuid = null;
    private int|string $serviceId;
    private int|string $carId;
    private null|int|string $dealershipId;
    private null|int|string $recommendationId;
    private null|int|string $agreementId;
    private string $communication;
    private null|string $comment;
    private null|int $mileage;
    private null|int $date;
    private null|int $time;
    private null|string $postUuid;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'serviceId');
        self::assetFieldAll($args, 'carId');
        self::assetFieldAll($args, 'dealershipId');
        self::assetFieldAll($args, 'communication');
//        self::assetFieldAll($args, 'comment');
//        self::assetFieldAll($args, 'mileage');
//        self::assetFieldAll($args, 'date');
//        self::assetFieldAll($args, 'time');

        Communication::assert($args['communication']);

        $self = new self();

        $self->serviceId = $args['serviceId'];
        $self->carId = $args['carId'];
        $self->dealershipId = $args['dealershipId'];
        $self->recommendationId = $args['recommendationId'] ?? null;
        $self->agreementId = $args['agreementId'] ?? null;
        $self->communication = $args['communication'];
        $self->comment = isset($args['comment']) && $args['comment'] != 'null' ? $args['comment'] : null;
        $self->mileage = $args['mileage'] ?? null;
        $self->date = $args['date'] ?? null;
        $self->time = $args['time'] ?? null;
        $self->postUuid = $args['postUuid'] ?? null;
        $self->uuid = $args['uuid'] ?? null;

        return $self;
    }

    public function getServiceId(): string|int
    {
        return $this->serviceId;
    }

    public function getCarId(): string|int
    {
        return $this->carId;
    }

    public function getDealershipId(): string|int
    {
        return $this->dealershipId;
    }

    public function getRecommendationId(): null|string|int
    {
        return $this->recommendationId;
    }

    public function getAgreementId(): null|string|int
    {
        return $this->agreementId;
    }

    public function getCommunication(): string
    {
        return $this->communication;
    }

    public function getComment(): null|string
    {
        return $this->comment;
    }

    public function getMileage(): null|int
    {
        return $this->mileage;
    }

    public function getDate(): null|int
    {
        return $this->date;
    }

    public function getTime(): null|int
    {
        return $this->time;
    }

    public function getPostUuid(): null|string
    {
        return $this->postUuid;
    }
}



