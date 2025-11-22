<?php

namespace App\Services\Catalog\Categories;

use App\Dto\Catalog\CategoryDto;
use App\Dto\Catalog\CategoryImportDto;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use App\Traits\Model\ToggleActive;
use Core\Exceptions\TranslatedException;
use Core\Traits\Auth\AuthGuardsTrait;
use Exception;
use Throwable;

class CategoryService
{
    use ToggleActive;
    use AuthGuardsTrait;

    public function create(CategoryDto $dto): Category
    {
        return $this->store(new Category(), $dto);
    }

    protected function store(Category $model, CategoryDto $dto): Category
    {
        if ($dto->getMain() && $dto->getParentId()) {
            throw new TranslatedException(
                __('Child category cannot be displayed as the main category on the main page')
            );
        }

        if (($guid = $dto->getGuid()) && ($this->isApiModerator() || $this->isSuperAdmin())) {
            $model->guid = $guid;
        }

        $model->active = $dto->getActive();
        $model->main = $dto->getMain();
        $model->parent_id = $dto->getParentId();
        $model->slug = $dto->getSlug();
        $model->enable_seer = $dto->getEnableSeer();

        $model->save();

        $this->saveTranslations($dto, $model);

        return $model;
    }

    protected function saveTranslations(CategoryDto|CategoryImportDto $dto, Category $model): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $model->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                    'seo_title' => $translation->getSeoTitle(),
                    'seo_description' => $translation->getSeoDescription(),
                    'seo_h1' => $translation->getSeoH1(),
                ]
            );
        }
    }

    public function update(CategoryDto $dto, Category $model): Category
    {
        return $this->store($model, $dto);
    }

    public function createByImport(CategoryImportDto $dto): void
    {
        $model = Category::updateOrCreate(
            [
                'id' => $dto->getId()
            ],
            [
                'active' => $dto->getActive(),
                'parent_id' => $dto->getParentId(),
                'slug' => $dto->getSlug(),
                'type' => $dto->getType()
            ]
        );

        $this->saveTranslations($dto, $model);
    }

    public function delete(Category $model): bool
    {
        try {
            return $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param Category $category
     * @return int|null
     * @throws Exception
     */
    public function getCategorySeer(Category $category): ?float
    {
        if (!$category->enable_seer) {
            return null;
        }
        $ids = categoryStorage()->getAllChildrenIds($category->id);

        return Product::whereIn('category_id', $ids)
            ->max('seer');
    }
}

