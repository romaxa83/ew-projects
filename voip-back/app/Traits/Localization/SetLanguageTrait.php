<?php

namespace App\Traits\Localization;

trait SetLanguageTrait
{
    public function setLanguage(?string $languageSlug): self
    {
        $localizationService = app('localization');

        if ($languageSlug && $localizationService->hasLang($languageSlug)) {
            return $this->setAttribute('lang', $languageSlug);
        }

        if (!$this->lang) {
            return $this->setAttribute('lang', $localizationService->getDefaultSlug());
        }

        return $this;
    }
}
