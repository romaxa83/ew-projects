<?php

namespace App\Services\Catalog\Certificate;

use App\Dto\Catalog\Certificate\TypeDto;
use App\Models\Catalog\Certificates\CertificateType;
use Exception;
use Throwable;

class TypeService
{
    public function create(TypeDto $dto): CertificateType
    {
        $model = new CertificateType();

        $this->fill($dto, $model);
        $model->save();

        return $model;
    }

    private function fill(TypeDto $dto, CertificateType $model): void
    {
        $model->type = $dto->getType();
    }

    public function update(TypeDto $dto, CertificateType $model): CertificateType
    {
        $this->fill($dto, $model);
        $model->save();

        $model->refresh();

        return $model;
    }

    public function remove(CertificateType $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}


