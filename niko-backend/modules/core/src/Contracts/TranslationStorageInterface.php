<?php

namespace WezomCms\Core\Contracts;

interface TranslationStorageInterface
{
    public const GLOBAL_NS = '*';

    public const CACHE_KEY = 'core-translations';

    /**
     * @param  string|null  $side
     * @param  string|null  $locale
     * @return array
     */
    public function getAlreadySavedTranslatedKeys(string $side = null, string $locale = null): array;

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
    );

    /**
     * @param  array  $translations
     * @return mixed
     */
    public function writeNewTranslations(array $translations);

    /**
     * @return array
     */
    public function getAllTranslations(): array;

    /**
     * @param  array  $criteria
     * @return mixed
     */
    public function deleteByCriteria(array $criteria);

    /**
     * @param  string  $namespace
     * @param  string  $side
     * @param  string  $key
     * @param  string|null  $locale
     * @return bool
     */
    public function hasSavedKey(string $namespace, string $side, string $key, ?string $locale = null): bool;
}
