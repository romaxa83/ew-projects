<?php

namespace App\Services\Promotion;

use App\DTO\NameTranslationDTO;
use App\DTO\Promotion\PromotionDTO;
use App\Models\Promotion\Promotion;
use App\Models\Promotion\PromotionTranslation;
use App\Services\BaseService;

class PromotionService extends BaseService
{
    public function __construct()
    {}

    public function create(PromotionDTO $dto): Promotion
    {
        try {
            $model = new Promotion();
            $model->active = $dto->getActive();
            $model->sort = $dto->getSort();
            $model->type = $dto->getType();
            $model->link = $dto->getLink();
            $model->department_id = $dto->getDepartmentId();
            $model->start_at = $dto->getStartAt();
            $model->finish_at = $dto->getFinishAt();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new PromotionTranslation();
                $t->model_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->text = $translation->getText();
                $t->save();
            }

            if(!$dto->emptyUserIds()){
                $this->attachUsers($model, $dto->getUserIds());
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(PromotionDTO $dto, Promotion $model): Promotion
    {
        try {
            $model->active = $dto->getActive();
            $model->sort = $dto->getSort();
            $model->type = $dto->getType();
            $model->link = $dto->getLink();
            $model->department_id = $dto->getDepartmentId();
            $model->start_at = $dto->getStartAt();
            $model->finish_at = $dto->getFinishAt();
            $model->save();

            $this->editTranslationsName($model, $dto);

            if(!$dto->emptyUserIds()){
                $this->attachUsers($model, $dto->getUserIds());
            }

            return $model->refresh();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function attachUsers(Promotion $model, array $ids): Promotion
    {
        try {
            if($model->isIndividual()){
                $model->users()->detach();
                $model->users()->attach($ids);
            } else {
                $model->users()->detach();
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function delete(Promotion $model): void
    {
        try {

            $model->delete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}


