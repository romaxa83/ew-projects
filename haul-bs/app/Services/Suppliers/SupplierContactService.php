<?php

namespace App\Services\Suppliers;

use App\Dto\Suppliers\SupplierContactDto;
use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;

class SupplierContactService
{
    public function __construct()
    {}

    public function create(SupplierContactDto $dto, Supplier $supplier): SupplierContact
    {
        $model = $this->fill(new SupplierContact(), $dto, $supplier);

        $model->save();

        return $model;
    }

    public function update(
        SupplierContact $model,
        SupplierContactDto $dto,
        Supplier $supplier
    ): SupplierContact
    {
        $model = $this->fill($model, $dto, $supplier);

        $model->save();

        return $model;
    }

    private function fill(SupplierContact $model, SupplierContactDto $dto, Supplier $supplier): SupplierContact
    {
        $model->supplier_id = $supplier->id;
        $model->name = $dto->name;
        $model->phone = $dto->phone;
        $model->phones = $dto->phones;
        $model->phone_extension = $dto->phoneExtension;
        $model->email = $dto->email;
        $model->emails = $dto->emails;
        $model->position = $dto->position;
        $model->is_main = $dto->isMain;

        return $model;
    }
}

