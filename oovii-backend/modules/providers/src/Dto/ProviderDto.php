<?php

namespace WezomCms\Providers\Dto;

class ProviderDto
{
    public $name;
    public $email;
    public $password;
    public $phone;
    public $company;
    public int $regionCode;
    public int $cityCode;
    public string $address;

    private function __construct()
    {}

    public static function byRegistry(array $data): self
    {
        $self = new self();

        $self->name = $data['name'];
        $self->email = $data['email'];
        $self->phone = $data['phone'];
        $self->password = $data['password'];
        $self->company = $data['company'];
        $self->regionCode = $data['region_code'];
        $self->cityCode = $data['city_code'];
        $self->address = $data['address'];

        return $self;
    }
}

