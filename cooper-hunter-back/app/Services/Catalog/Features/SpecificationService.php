<?php

namespace App\Services\Catalog\Features;

use App\Dto\Catalog\Features\Specifications\SpecificationDto;
use App\Models\Catalog\Features\Specification;

class SpecificationService
{
    public function create(SpecificationDto $dto): Specification
    {
        $s = new Specification();

        return $this->store($s, $dto);
    }

    protected function store(Specification $s, SpecificationDto $dto): Specification
    {
        $this->fill($dto, $s);

        $s->save();

        $this->saveTranslations($s, $dto);

        return $s;
    }

    protected function fill(SpecificationDto $dto, Specification $s): void
    {
        $s->active = $dto->getActive();
        $s->icon = $dto->getIcon();
    }

    protected function saveTranslations(Specification $s, SpecificationDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $s->translations()->updateOrCreate(
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

    public function update(Specification $s, SpecificationDto $dto): Specification
    {
        return $this->store($s, $dto);
    }

    public function toggle(Specification $s): Specification
    {
        $s->active = !$s->active;
        $s->save();

        return $s;
    }

    public function delete(Specification $s): bool
    {
        return $s->delete();
    }
}
