<?php

namespace App\Services\Companies;

use App\Dto\Companies\ShippingAddressDto;
use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use App\Traits\Model\ToggleActive;

class ShippingAddressService
{
    use ToggleActive;

    public function create(
        ShippingAddressDto $dto,
        Company $company
    ): ShippingAddress
    {
        $model = new ShippingAddress();

        $model->company_id = $company->id;
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    public function update(
        ShippingAddressDto $dto,
        ShippingAddress $model
    ): ShippingAddress
    {
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    protected function fill(
        ShippingAddress $model,
        ShippingAddressDto $dto
    ): void
    {
        $model->name = $dto->name;
        $model->active = $dto->active;
        $model->phone = $dto->phone;
        $model->email = $dto->email;
        $model->fax = $dto->fax;
        $model->receiving_persona = $dto->receivingPersona;
        $model->country_id = $dto->address->countryID;
        $model->state_id = $dto->address->stateID;
        $model->city = $dto->address->city;
        $model->address_line_1 = $dto->address->addressLine1;
        $model->address_line_2 = $dto->address->addressLine2;
        $model->zip = $dto->address->zip;
    }

    public function delete(ShippingAddress $model): bool
    {
        return $model->delete();
    }
}
