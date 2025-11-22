<?php

namespace App\Dto\Common;

use App\Foundations\ValueObjects\Phone;

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

        return $self;
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'company_name' => $this->companyName,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'phone' => $this->phone->getValue(),
        ];
    }
}
