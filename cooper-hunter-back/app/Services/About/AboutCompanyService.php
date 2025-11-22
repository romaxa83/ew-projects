<?php

namespace App\Services\About;

use App\Dto\About\About\AboutCompanyDto;
use App\Models\About\AboutCompany;

class AboutCompanyService
{
    public function createOrUpdate(AboutCompanyDto $dto): AboutCompany
    {
        $about = AboutCompany::query()->firstOrNew();

        $about->save();

        $this->saveTranslations($about, $dto);

        return $about;
    }

    protected function saveTranslations(AboutCompany $about, AboutCompanyDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $about->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'video_link' => $translation->getVideoLink(),
                    'title' => $translation->getTitle(),
                    'description' => $translation->getDescription(),
                    'short_description' => $translation->getShortDescription(),
                    'seo_title' => $translation->getSeoTitle(),
                    'seo_description' => $translation->getSeoDescription(),
                    'seo_h1' => $translation->getSeoH1(),
                    'additional_title' => $translation->getAdditionalTitle(),
                    'additional_description' => $translation->getAdditionalDescription(),
                    'additional_video_link' => $translation->getAdditionalVideoLink(),
                ]
            );
        }
    }
}
