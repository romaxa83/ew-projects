<?php

namespace App\Dto\Users;

use App\Foundations\ValueObjects\Phone;

class ProfileDto
{
    public string $firstName;
    public string $lastName;

    public Phone|null $phone;
    public string|null $phoneExtension;
    public array $phones;
    public string $lang;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->firstName = data_get($data, 'first_name');
        $self->lastName = data_get($data, 'last_name');
        $self->phone = data_get($data, 'phone')
            ? new Phone(data_get($data, 'phone'))
            : null
        ;
        $self->phoneExtension = data_get($data, 'phone_extension');
        $self->phones = data_get($data, 'phones')
            ? data_get($data, 'phones')
            : []
        ;
        $self->lang = data_get($data, 'language', default_lang()->slug);

        return $self;
    }
}


