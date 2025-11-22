<?php

namespace App\Services\About;

use App\Dto\About\ForMemberPages\ForMemberPageDto;
use App\Models\About\ForMemberPage;

class ForMemberPageService
{
    public function createOrUpdate(ForMemberPageDto $dto): ForMemberPage
    {
        $for = ForMemberPage::query()
            ->forMemberType($dto->getFor())
            ->firstOrNew();

        $this->fill($for, $dto);

        $for->save();

        $this->saveTranslations($for, $dto);

        return $for;
    }

    protected function fill(ForMemberPage $for, ForMemberPageDto $dto): void
    {
        $for->for_member_type = $dto->getFor();
    }

    protected function saveTranslations(ForMemberPage $for, ForMemberPageDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $for->translations()->updateOrCreate(
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
}
