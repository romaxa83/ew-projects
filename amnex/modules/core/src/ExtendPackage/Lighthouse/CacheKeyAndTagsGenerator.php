<?php

namespace Wezom\Core\ExtendPackage\Lighthouse;

use Illuminate\Contracts\Auth\Authenticatable;
use Nuwave\Lighthouse\Cache\CacheKeyAndTagsGenerator as LighthouseCacheKeyAndTagsGenerator;

class CacheKeyAndTagsGenerator extends LighthouseCacheKeyAndTagsGenerator
{
    public function key(
        ?Authenticatable $user,
        bool $isPrivate,
        string $parentName,
        int|string|null $id,
        string $fieldName,
        array $args,
        array $path,
    ): string {
        $key = parent::key($user, $isPrivate, $parentName, $id, $fieldName, $args, $path);

        return $this->addLanguage($key);
    }

    private function addLanguage(string $key): string
    {
        $language = (string)request()->header(config('translations.header'));
        if (!app('localization')->hasLang($language)) {
            return $key;
        }

        return implode(self::SEPARATOR, [$key, $language]);
    }
}
