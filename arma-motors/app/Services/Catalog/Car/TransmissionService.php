<?php

namespace App\Services\Catalog\Car;

use App\DTO\Catalog\Car\TransmissionDTO;
use App\DTO\Catalog\Car\TransmissionEditDTO;
use App\DTO\NameTranslationDTO;
use App\Models\Catalogs\Car\Transmission;
use App\Models\Catalogs\Car\TransmissionTranslation;
use App\Services\BaseService;
use DB;

class TransmissionService extends BaseService
{

    public function __construct()
    {}

    public function create(TransmissionDTO $dto): Transmission
    {
        DB::beginTransaction();
        try {
            $model = new Transmission();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new TransmissionTranslation();
                $t->transmission_id = $model->id;
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

    public function edit(TransmissionEditDTO $dto, Transmission $model): Transmission
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
