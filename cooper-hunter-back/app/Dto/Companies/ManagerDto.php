<?php

namespace App\Dto\Companies;

use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class ManagerDto
{
    public string $name;
    public Email $email;
    public ?Phone $phone;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->name = data_get($args, 'name');
        $dto->email = new Email(data_get($args, 'email'));
        $dto->phone = data_get($args, 'phone') !== null ? new Phone(data_get($args, 'phone')) : null;

        return $dto;
    }
}
