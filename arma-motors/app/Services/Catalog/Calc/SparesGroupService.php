<?php

namespace App\Services\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesGroupDTO;
use App\DTO\Catalog\Calc\SparesGroupEditDTO;
use App\DTO\Catalog\Calc\SparesGroupTranslationDTO;
use App\DTO\NameTranslationDTO;
use App\Models\Catalogs\Calc\SparesGroup;
use App\Models\Catalogs\Calc\SparesGroupTranslation;
use App\Services\BaseService;
use DB;

class SparesGroupService extends BaseService
{

    public function __construct()
    {}

    public function create(SparesGroupDTO $dto): SparesGroup
    {

        DB::beginTransaction();
        try {
            $model = new SparesGroup();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();
            $model->type = $dto->getType();
            $model->brand_id = $dto->getBrandId();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation SparesGroupTranslationDTO */
                $t = new SparesGroupTranslation();
                $t->group_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->unit = $translation->getUnit();
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

    public function edit(SparesGroupEditDTO $dto, SparesGroup $model): SparesGroup
    {
        DB::beginTransaction();
        try {
            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->type = $dto->getType() ?? $model->type;
            $model->brand_id = $dto->getBrandId() ?? $model->brand_id;
            $model->save();

            foreach ($dto->getTranslations() ?? [] as $translation){
                /** @var $translation SparesGroupTranslationDTO */
                $t = $model->translations()->where('lang', $translation->getLang())->first();
                $t->name = $translation->getName();
                $t->unit = $translation->getUnit();
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
}
