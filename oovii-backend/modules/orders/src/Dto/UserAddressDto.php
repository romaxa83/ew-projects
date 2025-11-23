<?php

namespace WezomCms\Orders\Dto;

use WezomCms\Orders\Services\SdekService;

class UserAddressDto
{
    private string $region_code;
    private string $city_code;
    private string|null $postal_code;
    private string $address;
    private string $name;

    private SdekService $sdekService;

    private function __construct()
    {
        $this->sdekService = resolve(SdekService::class);
    }

    public static function fromArray(array $data): self
    {
        $self = new self();

        $self->region_code = data_get($data, 'region_code');
        $self->city_code = data_get($data, 'city_code');
        $self->postal_code = data_get($data, 'postal_code');
        $self->address = data_get($data, 'address');
        $self->name = data_get($data, 'name');

        return $self;
    }

    public function getRegionCode(): string
    {
        return $this->region_code;
    }

    public function getCityCode(): string
    {
        return $this->city_code;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRegionName(): string
    {
        $region = $this->sdekService->getRegion($this->region_code);

        return $region ? $region->getRegion() : '';
    }

    public function getCityName(): string
    {
        $city = $this->sdekService->getCity($this->region_code, $this->city_code);

        return $city ? $city->getCity() : '';
    }
}
