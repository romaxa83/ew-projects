<?php

namespace App\Services\OurCases;

use App\Dto\Content\OurCaseCategories\OurCaseCategoryDto;
use App\Models\Content\OurCases\OurCaseCategory;

class OurCaseCategoryService
{
    public function create(OurCaseCategoryDto $dto): OurCaseCategory
    {
        return $this->update(new OurCaseCategory(), $dto);
    }

    public function update(OurCaseCategory $category, OurCaseCategoryDto $dto): OurCaseCategory
    {
        $this->fill($dto, $category);
        $category->save();

        $this->createOrUpdateTranslations($category, $dto);

        return $category;
    }

    protected function fill(OurCaseCategoryDto $dto, OurCaseCategory $category): void
    {
        $category->active = $dto->getActive();
        $category->slug = $dto->getSlug();
    }

    protected function createOrUpdateTranslations($category, OurCaseCategoryDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $category->translations()
                ->updateOrCreate(
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

    public function delete(OurCaseCategory $category): bool
    {
        return $category->delete();
    }
}
