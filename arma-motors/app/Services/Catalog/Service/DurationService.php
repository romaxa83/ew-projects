<?php

namespace App\Services\Catalog\Service;

use App\DTO\Catalog\Service\DurationDTO;
use App\DTO\Catalog\Service\DurationEditDTO;
use App\DTO\NameTranslationDTO;
use App\Models\Catalogs\Service\Duration;
use App\Models\Catalogs\Service\DurationTranslation;
use App\Models\Catalogs\Service\Service;
use App\Services\BaseService;
use DB;

class DurationService extends BaseService
{
    public function __construct()
    {}

    public function create(DurationDTO $dto): Duration
    {
        DB::beginTransaction();
        try {

            $model = new Duration();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new DurationTranslation();
                $t->duration_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->save();
            }

            // привязываем к сервисам
            if(!$dto->emptyServiceIds()){
                $model->services()->attach($dto->getServiceIds());
            }

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(DurationEditDTO $dto, Duration $model): Duration
    {
        DB::beginTransaction();
        try {

            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->save();

            $this->editTranslationsName($model, $dto);

            // привязываем к сервисам
            if(!$dto->emptyServiceIds()){
                $model->services()->detach();
                $model->services()->attach($dto->getServiceIds());
            }

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
