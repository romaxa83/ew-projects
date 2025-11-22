<?php

namespace App\Services\Catalog\Car;

use App\DTO\Catalog\Car\EngineVolumeDTO;
use App\DTO\Catalog\Car\EngineVolumeEditDTO;
use App\Models\Catalogs\Car\EngineVolume;
use App\Services\BaseService;
use DB;

class EngineVolumeService extends BaseService
{

    public function __construct()
    {}

    public function create(EngineVolumeDTO $dto): EngineVolume
    {
        DB::beginTransaction();
        try {
            $model = new EngineVolume();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();
            $model->volume = $dto->getVolume();

            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(EngineVolumeEditDTO $dto, EngineVolume $model): EngineVolume
    {
        DB::beginTransaction();
        try {
            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->volume = $dto->getVolume() ?? $model->volume;
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
