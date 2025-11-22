<?php

namespace App\Services\Catalog\Car;

use App\DTO\Catalog\Calc\WorkDTO;
use App\DTO\Catalog\Calc\WorkEditDTO;
use App\DTO\Catalog\Car\FuelDTO;
use App\DTO\Catalog\Car\FuelEditDTO;
use App\Models\Catalogs\Calc\Work;
use App\Models\Catalogs\Calc\WorkTranslation;
use App\DTO\NameTranslationDTO;
use App\Models\Catalogs\Car\Fuel;
use App\Models\Catalogs\Car\FuelTranslation;
use App\Services\BaseService;
use DB;

class FuelService extends BaseService
{

    public function __construct()
    {}

    public function create(FuelDTO $dto): Fuel
    {
        DB::beginTransaction();
        try {
            $model = new Fuel();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new FuelTranslation();
                $t->model_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->save();
            }

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(FuelEditDTO $dto, Fuel $model): Fuel
    {
        DB::beginTransaction();
        try {
            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->save();

            $this->editTranslationsName($model, $dto);

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
