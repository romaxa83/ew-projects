<?php

namespace WezomCms\Translates\Repositories;

use WezomCms\Core\Models\Translation;

class TranslateRepository
{
    private function query()
    {
        return Translation::query();
    }

    public function getTranslates()
    {
        return $this->query()
            ->select('key', 'locale', 'text')
            ->where('namespace', Translation::API_NAMESPACE)
            ->where('side', Translation::SIDE_SITE)
            ->get()
            ->toArray();
    }

    public function getTranslateByKey(string $key)
    {
        return $this->query()
            ->where('namespace', Translation::API_NAMESPACE)
            ->where('side', Translation::SIDE_SITE)
            ->where('key', $key)
            ->get();
    }

    public function getTranslatesByNamespace(string $namespace)
    {
        return $this->query()
            ->where('namespace', $namespace)
            ->where('side', Translation::SIDE_SITE)
            ->get();
    }
}
