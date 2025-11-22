<?php

namespace App\Services\Catalog\Service;

use App\DTO\Catalog\Service\ServiceDTO;
use App\DTO\Catalog\Service\ServiceEditDTO;
use App\DTO\NameTranslationDTO;
use App\Events\ChangeHashEvent;
use App\Models\Catalogs\Service\Service;
use App\Models\Catalogs\Service\ServiceTranslation;
use App\Models\Hash;
use App\Services\BaseService;
use DB;

class ServiceService extends BaseService
{
    public function __construct()
    {}

    public function create(ServiceDTO $dto): Service
    {
        DB::beginTransaction();
        try {

            $model = new Service();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();
            $model->alias = $dto->getAlias();
            $model->parent_id = $dto->getParentID();
            $model->icon = $dto->getIcon();
            $model->for_guest = $dto->getForGuest();
            $model->time_step = $dto->getTimeStep();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new ServiceTranslation();
                $t->service_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->save();
            }

            event(new ChangeHashEvent(Hash::ALIAS_SERVICE));

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(ServiceEditDTO $dto, Service $model): Service
    {
        DB::beginTransaction();
        try {

            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->parent_id = $dto->getParentId() ?? $model->parent_id;
            $model->time_step = $dto->getTimeStep() ?? $model->time_step;
            $model->save();

            $this->editTranslationsName($model, $dto);

            event(new ChangeHashEvent(Hash::ALIAS_SERVICE));

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}





