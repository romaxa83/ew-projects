<?php

namespace App\Services\OurCases;

use App\Dto\Content\OurCases\OurCaseDto;
use App\Models\Content\OurCases\OurCase;

class OurCaseService
{
    public function create(OurCaseDto $dto): OurCase
    {
        return $this->update(new OurCase(), $dto);
    }

    public function update(OurCase $ourCase, OurCaseDto $dto): OurCase
    {
        $this->fill($dto, $ourCase);
        $ourCase->save();

        $ourCase->products()->sync($dto->getProductIds());

        $this->createOrUpdateTranslations($ourCase, $dto);

        return $ourCase;
    }

    protected function fill(OurCaseDto $dto, OurCase $ourCase): void
    {
        $ourCase->active = $dto->getActive();
        $ourCase->category()->associate($dto->getOurCaseCategoryId());
    }

    protected function createOrUpdateTranslations(OurCase $ourCase, OurCaseDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $ourCase->translations()
                ->updateOrCreate(
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

    public function delete(OurCase $ourCase): bool
    {
        return $ourCase->delete();
    }
}
