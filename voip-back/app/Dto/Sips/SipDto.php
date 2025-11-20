<?php

namespace App\Dto\Sips;

final class SipDto
{
    public ?string $number;
    public string $password;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->number = data_get($args, 'number');
        $self->password = data_get($args, 'password');

        return $self;
    }
}

