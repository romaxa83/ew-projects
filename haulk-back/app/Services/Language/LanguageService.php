<?php

namespace App\Services\Language;

use App\Models\Language;
use Cache;
use Config;
use Schema;

class LanguageService
{

    public function load(): void
    {
        $languages = Cache::remember(
            'languages',
            now()->addMinutes(60),
            function () {
                return Language::all()->toArray();
            }
        );

        Config::set('languages', $languages);
    }

    public function hasTable(): bool
    {
        return Schema::hasTable(Language::TABLE_NAME);
    }
}
