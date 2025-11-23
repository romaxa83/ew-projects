<?php

namespace App\Services\Partners;

use App\Dto\Partners\PartnerDto;
use App\Models\Partners\Partner;

class PartnerService
{
    public function create(PartnerDto $dto): Partner
    {
        return $this->fill(new Partner(), $dto);
    }

    public function update(Partner $model, PartnerDto $dto): Partner
    {
        return $this->fill($model, $dto);
    }

    public function fill(Partner $model, PartnerDto $dto, bool $save = true): Partner
    {
        $model->name = $dto->name;
        $model->division_id = $dto->divisionId;
        $model->contact_person = $dto->contactPerson;
        $model->email = $dto->email;
        $model->phone = $dto->phone;

        if($save) $model->save();

        return $model;
    }
}
