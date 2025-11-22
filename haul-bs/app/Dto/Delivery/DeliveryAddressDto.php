<?php

namespace App\Dto\Delivery;

class DeliveryAddressDto
{
    public string|null $address;
    public string|null $city;
    public string|null $state;
    public string $zip;

    public static function byArgs(array $data): static
    {
        $self = new static();

        $self->address = data_get($data, 'address');
        $self->city = data_get($data, 'city');
        $self->state = data_get($data, 'state');
        $self->zip = data_get($data, 'zip');

        return $self;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getZip(): string
    {
        return $this->zip;
    }
}
