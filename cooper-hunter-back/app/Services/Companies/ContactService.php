<?php

namespace App\Services\Companies;

use App\Dto\Companies\CompanyDto;
use App\Dto\Companies\ContactDto;
use App\Enums\Companies\ContactType;
use App\Models\Companies\Company;
use App\Models\Companies\Contact;

class ContactService
{
    public function createContacts(
        Company $model,
        CompanyDto $dto
    ): void
    {
        $this->create($dto->contactAccount, $model, ContactType::ACCOUNT());
        $this->create($dto->contactOrder, $model, ContactType::ORDER());
    }

    public function updateContacts(
        Company $model,
        CompanyDto $dto
    ): void
    {
        $this->update($dto->contactAccount, $model, ContactType::ACCOUNT());
        $this->update($dto->contactOrder, $model, ContactType::ORDER());
    }

    public function create(
        ContactDto $dto,
        Company $company,
        ContactType $type
    ): Contact
    {
        $model = new Contact();

        $model->company_id = $company->id;
        $model->type = $type;
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    public function update(
        ContactDto $dto,
        Company $company,
        ContactType $type
    ): Contact
    {
        $model = $company->contacts->where('type', $type)->first();
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    protected function fill(
        Contact $model,
        ContactDto $dto
    ): void
    {
        $model->name = $dto->name;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
        $model->country_id = $dto->address->countryID;
        $model->state_id = $dto->address->stateID;
        $model->city = $dto->address->city;
        $model->address_line_1 = $dto->address->addressLine1;
        $model->address_line_2 = $dto->address->addressLine2;
        $model->zip = $dto->address->zip;
        $model->po_box = $dto->address->poBox;
    }
}
