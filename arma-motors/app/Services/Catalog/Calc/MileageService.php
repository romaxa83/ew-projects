<?php

namespace App\Services\Catalog\Calc;

use App\DTO\Catalog\Calc\MileageDTO;
use App\DTO\Catalog\Calc\MileageEditDTO;
use App\Models\Catalogs\Calc\Mileage;
use App\Services\BaseService;
use DB;

class MileageService extends BaseService
{

    public function __construct()
    {}

    public function create(MileageDTO $dto): Mileage
    {
        DB::beginTransaction();
        try {
            $model = new Mileage();
            $model->value = $dto->getValue();
            $model->active = $dto->getActive();

            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(MileageEditDTO $dto, Mileage $model): Mileage
    {
        DB::beginTransaction();
        try {
            $model->value = $dto->getValue() ?? $model->value;
            $model->active = $dto->getActive() ?? $model->active;
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
