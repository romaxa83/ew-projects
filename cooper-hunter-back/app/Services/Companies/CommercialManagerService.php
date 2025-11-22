<?php

namespace App\Services\Companies;

use App\Dto\Companies\ManagerDto;
use App\Models\Companies\CommercialManager;
use App\Models\Companies\Company;

class CommercialManagerService
{
    public function createOrUpdate(
        Company $model,
        ManagerDto $dto
    ): void
    {
        if($model->commercialManager){
            $this->update($model->commercialManager, $dto);
        } else {
            $this->create($model, $dto);
        }
    }

    public function create(Company $company, ManagerDto $dto): CommercialManager
    {
        $model = new CommercialManager();

        $model->company_id = $company->id;
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    public function update(CommercialManager $model, ManagerDto $dto): CommercialManager
    {
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    protected function fill(CommercialManager $model, ManagerDto $dto): void
    {
        $model->name = $dto->name;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
    }
}
