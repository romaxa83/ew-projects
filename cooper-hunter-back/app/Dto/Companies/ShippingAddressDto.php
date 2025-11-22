<?php

namespace App\Dto\Companies;

use App\Dto\Utilities\Address\AddressDto;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class ShippingAddressDto
{
    public ?int $id;
    public string $name;
    public bool $active;
    public Phone $phone;
    public Email $email;
    public ?Phone $fax;
    public string $receivingPersona;

    public AddressDto $address;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->id = data_get($args, 'id');
        $dto->name = data_get($args, 'name');
        $dto->active = data_get($args, 'active', true);
        $dto->phone = new Phone(data_get($args, 'phone'));
        $dto->email = new Email(data_get($args, 'email'));
        $dto->fax = empty(data_get($args, 'fax')) ? null : new Phone(data_get($args, 'fax'));
        $dto->receivingPersona = data_get($args, 'receiving_persona');

        $dto->address = AddressDto::byArgs([
            'country_code' => data_get($args, 'country_code'),
            'state_id' => data_get($args, 'state_id'),
            'city' => data_get($args, 'city'),
            'address_line_1' => data_get($args, 'address_line_1'),
            'address_line_2' => data_get($args, 'address_line_2'),
            'zip' => data_get($args, 'zip'),
        ]);

        return $dto;
    }
}

