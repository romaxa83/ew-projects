<?php

namespace App\Services\Catalog\Certificate;

use App\Dto\Catalog\Certificate\CertificateDto;
use App\Models\Catalog\Certificates\Certificate;
use Exception;
use Throwable;

class CertificateService
{
    public function create(CertificateDto $dto): Certificate
    {
        $model = new Certificate();

        $this->fill($dto, $model);
        $model->save();

        // todo Федя зарефактори эту дичь
        $model->type_name = $model->type->type;

        return $model;
    }

    private function fill(CertificateDto $dto, Certificate $model): void
    {
        $model->number = $dto->getNumber();
        $model->link = $dto->getLink();
        $model->certificate_type_id = $dto->getTypeId();
    }

    public function update(CertificateDto $dto, Certificate $model): Certificate
    {
        $this->fill($dto, $model);
        $model->save();

        $model->refresh();

        return $model;
    }

    public function remove(Certificate $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    private function typeName()
    {

    }
}


