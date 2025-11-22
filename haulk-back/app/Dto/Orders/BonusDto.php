<?php

namespace App\Dto\Orders;

use App\Dto\BaseDto;

/**
 * @property-read int|null $id
 * @property-read string $type
 * @property-read float $price
 * @property-read string|null $to
 */
class BonusDto extends BaseDto
{
    protected ?int $id;
    protected string $type;
    protected float $price;
    protected ?string $to;

    public static function init(array $args): self
    {
        $dto = new self();

        $dto->id = $args['id'] ?? null;
        $dto->type = $args['type'];
        $dto->price = (float)$args['price'];
        $dto->to = $args['to'] ?? null;

        return $dto;
    }
}
