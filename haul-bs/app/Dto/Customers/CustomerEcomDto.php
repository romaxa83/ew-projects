<?php

namespace App\Dto\Customers;

use App\Enums\Customers\CustomerType;
use App\Foundations\ValueObjects\Email;

class CustomerEcomDto
{
    public string $firstName;
    public string $lastName;
    public string $type;
    public Email $email;
    public array $tags = [];


    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->firstName = data_get($data, 'first_name');
        $self->lastName = data_get($data, 'last_name');
        $self->email = new Email(data_get($data, 'email'));
        $self->type = CustomerType::EComm();

        return $self;
    }
}
