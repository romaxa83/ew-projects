<?php

namespace App\Services\Catalog;

use App\Dto\Catalog\FeatureDto;
use App\Models\Catalog\Features\Feature;
use App\Traits\Model\ToggleActive;
use Core\Traits\Auth\AuthGuardsTrait;
use Exception;
use Illuminate\Support\Str;
use Throwable;

class FeatureService
{
    use ToggleActive;
    use AuthGuardsTrait;

    public function create(FeatureDto $dto): Feature
    {
        $feature = new Feature();

        $this->fill($dto, $feature);
        $feature->save();

        $this->setUniqueForMobile($feature);

        $this->saveTranslations($feature, $dto);

        return $feature;
    }

    protected function saveTranslations(Feature $model, FeatureDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $model->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                    'slug' => Str::slug($translation->getTitle()),
                    'description' => $translation->getDescription(),
                ]
            );
        }
    }

    private function fill(FeatureDto $dto, Feature $model): void
    {
        if (($guid = $dto->getGuid()) && ($this->isApiModerator() || $this->isSuperAdmin())) {
            $model->guid = $guid;
        }

        $model->active = $dto->getActive();
        $model->display_in_mobile = $dto->getDisplayInMobile();
        $model->display_in_web = $dto->getDisplayInWeb();
        $model->display_in_filter = $dto->getDisplayInFilter();
    }

    protected function setUniqueForMobile(Feature $feature): void
    {
        if ($feature->display_in_mobile) {
            Feature::query()
                ->where('display_in_mobile', true)
                ->whereKeyNot($feature->id)
                ->update(
                    [
                        'display_in_mobile' => false
                    ]
                );
        }
    }

    public function update(FeatureDto $dto, Feature $model): Feature
    {
        $this->fill($dto, $model);
        $model->save();

        $this->setUniqueForMobile($model);

        $this->saveTranslations($model, $dto);

        $model->refresh();

        return $model;
    }

    public function delete(Feature $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
