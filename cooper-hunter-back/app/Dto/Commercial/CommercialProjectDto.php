<?php

namespace App\Dto\Commercial;

use App\Traits\Dto\CountryIDFromDB;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class CommercialProjectDto
{
    use CountryIDFromDB;

    private string $name;
    private string $addressLine1;
    private ?string $addressLine2;
    private string $city;
    private int $stateId;
    private int $countryId;
    private string $zip;
    private string $firstName;
    private string $lastName;
    private Phone $phone;
    private Email $email;
    private string $companyName;
    private string $companyAddress;
    private ?string $description;
    private string $estimateStartDate;
    private string $estimateEndDate;
    public ?string $requestUntil;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->name = $args['name'];
        $dto->addressLine1 = $args['address_line_1'];
        $dto->addressLine2 = $args['address_line_2'] ?? null;
        $dto->city = $args['city'];
        $dto->stateId = $args['state_id'];
        $dto->countryId = self::countryIdFromDB($args['country_code']);
        $dto->zip = $args['zip'];
        $dto->firstName = $args['first_name'];
        $dto->lastName = $args['last_name'];
        $dto->phone = new Phone($args['phone']);
        $dto->email = new Email($args['email']);
        $dto->companyName = $args['company_name'];
        $dto->companyAddress = $args['company_address'];
        $dto->description = $args['description'] ?? null;
        $dto->estimateStartDate = $args['estimate_start_date'];
        $dto->estimateEndDate = $args['estimate_end_date'];
        $dto->requestUntil = $args['request_until'] ?? null;

        return $dto;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddressLine1(): string
    {
        return $this->addressLine1;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStateId(): int
    {
        return $this->stateId;
    }

    public function getCountryId(): int
    {
        return $this->countryId;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getCompanyAddress(): string
    {
        return $this->companyAddress;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getEstimateStartDate(): string
    {
        return $this->estimateStartDate;
    }

    public function getEstimateEndDate(): string
    {
        return $this->estimateEndDate;
    }
}
