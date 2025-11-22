<?php

namespace App\Services\Catalog\Region;

use App\DTO\Catalog\Region\CityDTO;
use App\DTO\NameTranslationDTO;
use App\Models\Catalogs\Region\City;
use App\Traits\Translations\TranslationCrud;
use DB;

class CityService
{
    use TranslationCrud;

    public function __construct()
    {}

    public function edit(CityDTO $dto, City $model): City
    {
        DB::beginTransaction();
        try {

            $model->region_id = $dto->getRegionId() ?? $model->region_id;
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

    public function toggleActive(City $model): City
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



