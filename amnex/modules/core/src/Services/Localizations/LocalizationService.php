<?php

namespace Wezom\Core\Services\Localizations;

use Illuminate\Database\Eloquent\Collection;
use Wezom\Core\Models\Language;

class LocalizationService
{
    private static ?Language $language = null;

    /**
     * @var Collection<Language>|null
     */
    private static ?Collection $languages = null;

    public function getDefaultSlug(): string
    {
        return $this->getDefault()->slug;
    }

    public function getDefault(): Language
    {
        if (is_null(self::$language)) {
            self::$language = Language::default()->first();
        }

        return self::$language;
    }

    public function hasLang(string $lang): bool
    {
        return $this->getAllLanguages()->has($lang);
    }

    /**
     * @return Collection<Language>
     */
    public function getAllLanguages(): Collection
    {
        if (is_null(self::$languages)) {
            self::$languages = Language::query()
                ->get()
                ->keyBy('slug');
        }

        return self::$languages;
    }
}
