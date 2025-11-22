<?php

namespace App\Dto\Stores\Distributors;

use App\ValueObjects\Point;

class CoordinatesDto
{
    private float $longitude;
    private float $latitude;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->longitude = $args['longitude'];
        $dto->latitude = $args['latitude'];

        return $dto;
    }

    public function asPoint(): Point
    {
        return new Point($this->getLongitude(), $this->getLatitude());
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }
}
