<?php

namespace App\Dto\Dealers;

use App\ValueObjects\Email;

class DealerRegisterDto
{
    public Email $email;
    public string $password;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->password = data_get($args, 'password');
        $dto->email = new Email(data_get($args, 'email'));

        return $dto;
    }
}

