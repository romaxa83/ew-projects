<?php

namespace App\Dto\Suppliers;

use App\Foundations\ValueObjects\Email;
use App\Foundations\ValueObjects\Phone;

class SupplierContactDto
{
    public string|int|null $id;
    public string $name;
    public Phone $phone;
    public array $phones;
    public string|null $phoneExtension;
    public Email $email;
    public array $emails;
    public string|null $position;
    public bool $isMain;

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->id = data_get($data, 'id');
        $self->name = data_get($data, 'name');
        $self->phone = new Phone(data_get($data, 'phone'));
        $self->phones = data_get($data, 'phones', []);
        $self->phoneExtension = data_get($data, 'phone_extension');
        $self->email = new Email(data_get($data, 'email'));
        $self->emails = data_get($data, 'emails', []);
        $self->position = data_get($data, 'position');
        $self->isMain = data_get($data, 'is_main');

        return $self;
    }
}


