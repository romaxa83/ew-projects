<?php

namespace App\Dto\Contacts;

use App\Dto\BaseDto;

/**
 * @property-read string|null $name
 * @property-read string|null $number
 * @property-read string|null $extension
 * @property-read string|null $notes
 */
class PhoneDto extends BaseDto
{
    protected ?string $name;
    protected ?string $number;
    protected ?string $extension;
    protected ?string $notes;

    public static function init(array $args): self
    {
        $dto = new self();
        $dto->name = $args['name'] ?? null;
        $dto->number = $args['number'] ? preg_replace("/\D+/", "", $args['number']) : null;
        $dto->extension = $args['extension'] ?? null;
        $dto->notes = $args['notes'] ?? null;

        return $dto;
    }
}
