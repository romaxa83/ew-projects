<?php

namespace WezomCms\Core\Foundation;

use Cache;
use WezomCms\Core\Contracts\TranslationStorageInterface;
use WezomCms\Core\Models\Translation;

class DatabaseTranslationStorage implements TranslationStorageInterface
{
    /**
     * @param  string|null  $side
     * @param  string|null  $locale
     * @return array
     */
    public function getAlreadySavedTranslatedKeys(string $side = null, string $locale = null): array
    {
        $query = Translation::query();

        if (null !== $side) {
            $query->where('side', $side);
        }

        if (null !== $locale) {
            $query->where('locale', $locale);
        }

        return $query->get()
            ->map(function (Translation $translation) {
                return $translation->full_key;
            })
            ->toArray();
    }

    /**
     * @param  string|null  $namespace
     * @param  string|null  $side
     * @param  string|null  $locale
     * @param  bool|null  $translated
     * @return mixed
     */
    public function getAlreadySaved(
        string $namespace = null,
        string $side = null,
        string $locale = null,
        ?bool $translated = null
    ) {
        $query = Translation::query();

        if (null !== $namespace) {
            $query->where('namespace', $namespace);
        }

        if (null !== $side) {
            $query->where('side', $side);
        }

        if (null !== $locale) {
            $query->where('locale', $locale);
        }

        if (null !== $translated) {
            $query->where('translated', $translated);
        }

        return $query->get();
    }

    /**
     * @param  array  $translations
     */
    public function writeNewTranslations(array $translations)
    {
        foreach ($translations as $key) {
            Translation::saveNewKey($key);
        }

        Cache::forget(static::CACHE_KEY);
    }

    /**
     * @return array
     */
    public function getAllTranslations(): array
    {
        $callback = function () {
            $result = [];
            Translation::all()
                ->each(function (Translation $row) use (&$result) {
                    $result[$row->namespace][$row->locale][$row->side . '.' . $row->key] = $row->text;
                });

            return $result;
        };

        return app()->environment() === 'production'
            ? Cache::rememberForever(static::CACHE_KEY, $callback)
            : $callback();
    }

    /**
     * @param  array  $criteria
     * @return mixed
     */
    public function deleteByCriteria(array $criteria)
    {
        return Translation::where($criteria)->delete();
    }

    /**
     * @param  string  $namespace
     * @param  string  $side
     * @param  string  $key
     * @param  string|null  $locale
     * @return bool
     */
    public function hasSavedKey(string $namespace, string $side, string $key, ?string $locale = null): bool
    {
        $query = Translation::query();

        $query->where('namespace', $namespace)
            ->where('side', $side)
            ->where('key', $key);

        if (null !== $locale) {
            $query->where('locale', $locale);
        }

        return $query->exists();
    }
}
