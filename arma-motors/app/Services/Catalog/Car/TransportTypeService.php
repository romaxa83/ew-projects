<?php

namespace App\Services\Catalog\Car;

use App\DTO\Catalog\Car\TransportTypeDTO;
use App\DTO\Catalog\Car\TransportTypeEditDTO;
use App\DTO\NameTranslationDTO;
use App\Models\Catalogs\Car\TransportType;
use App\Models\Catalogs\Car\TransportTypeTranslation;
use App\Services\BaseService;
use DB;

class TransportTypeService extends BaseService
{

    public function __construct()
    {}

    public function create(TransportTypeDTO $dto): TransportType
    {
        DB::beginTransaction();
        try {

            $model = new TransportType();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new TransportTypeTranslation();
                $t->transport_type_id = $model->id;
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

    public function edit(TransportTypeEditDTO $dto, TransportType $model): TransportType
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

