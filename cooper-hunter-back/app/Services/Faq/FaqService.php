<?php

namespace App\Services\Faq;

use App\Dto\Faq\FaqDto;
use App\Models\Faq\Faq;

class FaqService
{
    public function create(FaqDto $dto): Faq
    {
        $faq = new Faq();

        $this->fill($faq, $dto);

        $faq->save();

        $this->syncTranslations($faq, $dto);

        return $faq;
    }

    protected function fill(Faq $faq, FaqDto $dto): void
    {
        $faq->active = $dto->getActive();
    }

    protected function syncTranslations(Faq $faq, FaqDto $dto): void
    {
        foreach ($dto->getTranslations() ?? [] as $translation) {
            $faq->translations()->updateOrCreate(
                [
                    'language' => $translation->getLanguage(),
                ],
                [
                    'question' => $translation->getQuestion(),
                    'answer' => $translation->getAnswer(),
                    'seo_title' => $translation->getSeoTitle(),
                    'seo_description' => $translation->getSeoDescription(),
                    'seo_h1' => $translation->getSeoH1(),
                ]
            );
        }
    }

    public function update(Faq $faq, FaqDto $dto): Faq
    {
        $this->fill($faq, $dto);

        $this->syncTranslations($faq, $dto);

        $faq->save();

        return $faq;
    }

    public function toggle(Faq $faq): Faq
    {
        $faq->active = !$faq->active;

        $faq->save();

        return $faq;
    }

    public function delete(Faq $faq): bool
    {
        return $faq->delete();
    }
}
