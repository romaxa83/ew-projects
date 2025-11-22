<?php

namespace App\Services\Catalog\Solutions;

use App\Dto\Catalog\Solutions\Series\SolutionSeriesDto;
use App\Models\Catalog\Solutions\Series\SolutionSeries;

class SolutionSeriesService
{
    public function create(SolutionSeriesDto $dto): SolutionSeries
    {
        return $this->store($dto, new SolutionSeries());
    }

    protected function store(SolutionSeriesDto $dto, SolutionSeries $model): SolutionSeries
    {
        $this->fill($dto, $model);

        $model->save();

        $this->saveTranslations($dto, $model);

        return $model;
    }

    protected function fill(SolutionSeriesDto $dto, SolutionSeries $model): void
    {
        $model->slug = $dto->getSlug();
    }

    protected function saveTranslations(SolutionSeriesDto $dto, SolutionSeries $model): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $model->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'title' => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                ]
            );
        }
    }

    public function update(SolutionSeries $model, SolutionSeriesDto $dto): SolutionSeries
    {
        return $this->store($dto, $model);
    }

    public function delete(SolutionSeries $model): bool
    {
        return $model->delete();
    }
}
