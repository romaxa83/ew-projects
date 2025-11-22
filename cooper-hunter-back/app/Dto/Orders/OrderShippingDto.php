<?php

namespace App\Dto\Orders;

use App\Traits\Dto\CountryIDFromDB;
use App\ValueObjects\Phone;

class OrderShippingDto
{
    use CountryIDFromDB;

    private string $firstName;
    private string $lastName;
    private Phone $phone;

    private string $addressFirstLine;
    private ?string $addressSecondLine;
    private string $city;
    private string $countryId;
    private string $stateId;
    private string $zip;
    private int $deliveryType;
    private ?string $trkNumber = null;

    /**
     * @param array $args
     * @return OrderShippingDto
     */
    public static function byArgs(array $args): OrderShippingDto
    {
        $dto = new self();

        $dto->lastName = $args['last_name'];
        $dto->firstName = $args['first_name'];
        $dto->phone = new Phone($args['phone']);

        $dto->addressFirstLine = $args['address_first_line'];
        $dto->addressSecondLine = data_get($args, 'address_second_line');
        $dto->city = $args['city'];
        $dto->stateId = $args['state_id'];
        $dto->countryId = self::countryIdFromDB($args['country_code']);
        $dto->zip = $args['zip'];
        $dto->deliveryType = (int)$args['delivery_type'];
        $dto->trkNumber = data_get($args, 'trk_number');

        return $dto;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    public function getAddressFirstLine(): string
    {
        return $this->addressFirstLine;
    }

    public function getAddressSecondLine(): ?string
    {
        return $this->addressSecondLine;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStateID(): int
    {
        return $this->stateId;
    }

    public function getCountryID(): int
    {
        return $this->countryId;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function getDeliveryType(): int
    {
        return $this->deliveryType;
    }

    public function getTrkNumber(): ?string
    {
        return $this->trkNumber;
    }
}

