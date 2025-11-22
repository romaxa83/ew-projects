<?php

namespace App\Services\Support;

use App\DTO\NameTranslationDTO;
use App\DTO\Support\SupportCategoryDTO;
use App\DTO\Support\SupportCategoryEditDTO;
use App\Models\Support\Category;
use App\Models\Support\CategoryTranslation;
use App\Services\BaseService;
use DB;

class CategoryService extends BaseService
{
    public function __construct()
    {}

    public function create(SupportCategoryDTO $dto): Category
    {
        DB::beginTransaction();
        try {

            $model = new Category();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation NameTranslationDTO */
                $t = new CategoryTranslation();
                $t->category_id = $model->id;
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

    public function edit(SupportCategoryEditDTO $dto, Category $model): Category
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
