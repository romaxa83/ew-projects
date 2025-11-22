<?php

namespace App\Services\Catalog\Calc;

use App\DTO\Catalog\Calc\WorkDTO;
use App\DTO\Catalog\Calc\WorkEditDTO;
use App\Models\Catalogs\Calc\Work;
use App\Models\Catalogs\Calc\WorkTranslation;
use App\DTO\NameTranslationDTO;
use App\Services\BaseService;
use DB;

class WorkService extends BaseService
{

    public function __construct()
    {}

    public function create(WorkDTO $dto): Work
    {
        DB::beginTransaction();
        try {
            $model = new Work();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new WorkTranslation();
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

    public function edit(WorkEditDTO $dto, Work $model): Work
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
