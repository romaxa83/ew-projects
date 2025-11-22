<?php

namespace App\Services\Customers;

use App\Dto\Customers\AddressDto;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Carbon\CarbonImmutable;

class CustomerAddressService
{
    public function __construct()
    {}

    public function create(AddressDto $dto, Customer $customer): Address
    {
        return make_transaction(function () use ($dto, $customer){

            $model = $this->fill(new Address(), $dto);
            $model->customer_id = $customer->id;

            if($model->isDefault() && $customer->defaultAddress()){

                $customer->defaultAddress()->update([
                    'is_default' => false,
                    'sort' => $model->customer->defaultAddress()->fromEcomm()
                        ? $customer->defaultAddress()->created_at->timestamp * 2
                        : $customer->defaultAddress()->created_at->timestamp
                ]);
            }

            $model->save();

            return $model;
        });
    }

    public function update(
        Address $model,
        AddressDto $dto
    ): Address
    {
        return make_transaction(function () use ($model, $dto){
            $model = $this->fill($model, $dto);

            if($model->isDefault() && $model->customer->defaultAddress()){
                $model->customer->defaultAddress()->update([
                    'is_default' => false,
                    'sort' => $model->customer->defaultAddress()->fromEcomm()
                        ? $model->customer->defaultAddress()->created_at->timestamp * 2
                        : $model->customer->defaultAddress()->created_at->timestamp
                ]);
            }

            $model->save();

            return $model;
        });
    }

    protected function fill(Address $model, AddressDto $dto): Address
    {
        $timestamp = CarbonImmutable::now()->timestamp;

        $model->is_default = $dto->isDefault;
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->company_name = $dto->companyName;
        $model->address = $dto->address;
        $model->city = $dto->city;
        $model->state = $dto->state;
        $model->zip = $dto->zip;
        $model->phone = $dto->phone;
        $model->type = $dto->type;
        $model->from_ecomm = $dto->fromEcomm;
        if($dto->isDefault){
            $model->sort = $timestamp * 3;
        } else {
            $model->sort = $dto->fromEcomm
                ? $timestamp * 2
                : $timestamp;
        }

        return $model;
    }

    public function delete(Address $model): bool
    {
        return $model->delete();
    }
}

