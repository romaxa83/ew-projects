<?php

namespace App\DTO\Order;

use App\Traits\AssetData;
use App\Types\Communication;

class OrderBodyDTO
{
    use AssetData;

    private int|string $serviceId;
    private int|string $carId;
    private int|string $dealershipId;
    private string $communication;
    private null|string $comment;
    private int $date;
    private int $time;
    private null|int|string $recommendationId;
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
        self::assetFieldAll($args, 'date');
        self::assetFieldAll($args, 'time');

        Communication::assert($args['communication']);

        $self = new self();

        $self->serviceId = $args['serviceId'];
        $self->carId = $args['carId'];
        $self->dealershipId = $args['dealershipId'];
        $self->communication = $args['communication'];
        $self->comment = isset($args['comment']) && $args['comment'] != 'null' ? $args['comment'] : null;
        $self->date = $args['date'];
        $self->time = $args['time'];
        $self->recommendationId = $args['recommendationId'] ?? null;
        $self->postUuid = $args['postUuid'] ?? null;

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

    public function getCommunication(): string
    {
        return $this->communication;
    }

    public function getComment(): null|string
    {
        return $this->comment;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getRecommendationId(): null|string|int
    {
        return $this->recommendationId;
    }

    public function getPostUuid(): null|string
    {
        return $this->postUuid;
    }
}




