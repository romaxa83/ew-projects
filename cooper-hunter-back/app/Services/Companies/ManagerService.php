<?php

namespace App\Services\Companies;

use App\Dto\Companies\ManagerDto;
use App\Models\Companies\Company;
use App\Models\Companies\Manager;

class ManagerService
{
    public function createOrUpdate(
        Company $model,
        ManagerDto $dto
    ): void
    {
        if($model->manager){
            $this->update($model->manager, $dto);
        } else {
            $this->create($model, $dto);
        }
    }

    public function create(Company $company, ManagerDto $dto): Manager
    {
        $model = new Manager();

        $model->company_id = $company->id;
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    public function update(Manager $model, ManagerDto $dto): Manager
    {
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    protected function fill(Manager $model, ManagerDto $dto): void
    {
        $model->name = $dto->name;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
    }
}
