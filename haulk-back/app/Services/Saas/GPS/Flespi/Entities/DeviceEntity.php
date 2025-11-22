<?php

namespace App\Services\Saas\GPS\Flespi\Entities;

class DeviceEntity
{
    public int $id;
    public ?string $name;
    public string $imei;
    public ?string $phone;

    public function __construct(array $data)
    {
        $this->id = data_get($data,'id');
        $this->name = data_get($data,'name');
        $this->imei = data_get($data,'configuration.ident');
        $this->phone = data_get($data,'configuration.phone');
    }
}
