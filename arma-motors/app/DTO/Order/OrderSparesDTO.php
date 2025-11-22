<?php

namespace App\DTO\Order;

use App\Traits\AssetData;
use App\Types\Communication;

class OrderSparesDTO
{
    use AssetData;

    private int|string $serviceId;
    private int|string $carId;
    private string $communication;
    private string $comment;
    private null|int|string $recommendationId;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'serviceId');
        self::assetFieldAll($args, 'carId');
        self::assetFieldAll($args, 'communication');
        self::assetFieldAll($args, 'comment');

        Communication::assert($args['communication']);

        $self = new self();

        $self->serviceId = $args['serviceId'];
        $self->carId = $args['carId'];
        $self->communication = $args['communication'];
        $self->comment = $args['comment'];
        $self->recommendationId = $args['recommendationId'] ?? null;

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

    public function getCommunication(): string
    {
        return $this->communication;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getRecommendationId(): null|string|int
    {
        return $this->recommendationId;
    }
}





