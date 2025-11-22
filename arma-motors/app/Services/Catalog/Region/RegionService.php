<?php

namespace App\Services\Catalog\Region;

use App\DTO\Catalog\Region\RegionDTO;
use App\Models\Catalogs\Region\Region;
use App\Traits\Translations\TranslationCrud;
use DB;

class RegionService
{
    use TranslationCrud;

    public function __construct()
    {}

    public function edit(RegionDTO $dto, Region $model): Region
    {
        DB::beginTransaction();
        try {

            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->save();

            $this->editName($model, $dto);

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function toggleActive(Region $model): Region
    {
        try {
            $model->active = !$model->active;
            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}




