<?php

namespace App\Dto\Orders;

use App\Dto\BaseDto;
use Illuminate\Support\Str;

/**
 * @property-read int|null $id
 * @property-read bool|null $inop
 * @property-read bool|null $enclosed
 * @property-read string|null $vin
 * @property-read string|null $year
 * @property-read string $make
 * @property-read string $model
 * @property-read int $typeId
 * @property-read string|null $color
 * @property-read string|null $licensePlate
 * @property-read string|null $odometer
 * @property-read string|null $stockNumber
 */
class VehicleDto extends BaseDto
{
    protected ?int $id;
    protected ?bool $inop;
    protected ?bool $enclosed;
    protected ?string $vin;
    protected ?string $year;
    protected string $make;
    protected string $model;
    protected int $typeId;
    protected ?string $color;
    protected ?string $licensePlate;
    protected ?string $odometer;
    protected ?string $stockNumber;

    public static function init(array $args): self
    {
        $dto = new self();

        $dto->id = $args['id'] ?? null;
        $dto->inop = !empty($args['inop']);
        $dto->enclosed = !empty($args['enclosed']);
        $dto->vin = !empty($args['vin']) ? Str::upper($args['vin']) : null;
        $dto->year = $args['year'] ?? null;
        $dto->make = $args['make'];
        $dto->model = $args['model'];
        $dto->typeId = $args['type_id'];
        $dto->color = $args['color'] ?? null;
        $dto->licensePlate = $args['license_plate'] ?? null;
        $dto->odometer = $args['odometer'] ?? null;
        $dto->stockNumber = $args['stock_number'] ?? null;

        return $dto;
    }
}
