<?php

namespace App\Dto\Customers;

use App\Foundations\ValueObjects\Phone;
use App\Models\Customers\Address;

class AddressDto
{
    public string $firstName;
    public string $lastName;
    public ?string $companyName;
    public string $address;
    public string $city;
    public string $state;
    public string $zip;
    public Phone $phone;
    public string $type;
    public bool $isDefault;
    public bool $fromEcomm;


    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->firstName = $data['first_name'];
        $self->lastName = $data['last_name'];
        $self->companyName = $data['company_name'] ?? null;
        $self->address = $data['address'];
        $self->city = $data['city'];
        $self->state = $data['state'];
        $self->zip = $data['zip'];
        $self->phone = new Phone($data['phone']);

        $self->type = $data['type'] ?? Address::DEFAULT_TYPE;
        $self->isDefault = $data['is_default'];
        $self->fromEcomm = $data['from_ecomm'] ?? false;

        return $self;
    }
}
