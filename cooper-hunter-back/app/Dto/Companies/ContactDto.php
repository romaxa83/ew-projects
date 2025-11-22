<?php

namespace App\Dto\Companies;

use App\Dto\Utilities\Address\AddressDto;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class ContactDto
{
    public string $name;
    public Email $email;
    public Phone $phone;

    public AddressDto $address;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->name = data_get($args, 'name');
        $dto->email = new Email(data_get($args, 'email'));
        $dto->phone = new Phone(data_get($args, 'phone'));

        $dto->address = AddressDto::byArgs([
            'country_code' => data_get($args, 'country_code'),
            'state_id' => data_get($args, 'state_id'),
            'city' => data_get($args, 'city'),
            'address_line_1' => data_get($args, 'address_line_1'),
            'address_line_2' => data_get($args, 'address_line_2'),
            'zip' => data_get($args, 'zip'),
            'po_box' => data_get($args, 'po_box'),
        ]);

        $dto->media = data_get($args, 'media', []);

        return $dto;
    }
}
