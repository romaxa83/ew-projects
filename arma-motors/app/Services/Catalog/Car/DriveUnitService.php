<?php

namespace App\Services\Catalog\Car;

use App\DTO\Catalog\Car\DriveUnitDTO;
use App\DTO\Catalog\Car\DriveUnitEditDTO;
use App\Models\Catalogs\Car\DriveUnit;
use App\Services\BaseService;
use DB;

class DriveUnitService extends BaseService
{

    public function __construct()
    {}

    public function create(DriveUnitDTO $dto): DriveUnit
    {
        DB::beginTransaction();
        try {
            $model = new DriveUnit();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();
            $model->name = $dto->getName();

            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(DriveUnitEditDTO $dto, DriveUnit $model): DriveUnit
    {
        DB::beginTransaction();
        try {
            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->name = $dto->getName() ?? $model->name;
            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

