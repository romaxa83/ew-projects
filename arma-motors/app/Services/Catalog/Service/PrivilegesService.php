<?php

namespace App\Services\Catalog\Service;

use App\DTO\Catalog\Service\PrivilegesDTO;
use App\DTO\Catalog\Service\PrivilegesEditDTO;
use App\DTO\NameTranslationDTO;
use App\Models\Catalogs\Service\Privileges;
use App\Models\Catalogs\Service\PrivilegesTranslation;
use App\Services\BaseService;
use DB;

class PrivilegesService extends BaseService
{

    public function __construct()
    {}

    public function create(PrivilegesDTO $dto): Privileges
    {
        DB::beginTransaction();
        try {

            $model = new Privileges();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new PrivilegesTranslation();
                $t->privileges_id = $model->id;
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

    public function edit(PrivilegesEditDTO $dto, Privileges $model): Privileges
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
