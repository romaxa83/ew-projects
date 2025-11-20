<?php

namespace WezomCms\Core\Commands\Translations;

use WezomCms\Core\Enums\TranslationSide;
use WezomCms\Core\Models\Translation;

class FindMissingCommand extends ScanCommand
{
    protected $signature = 'translations:find-missing';

    protected $description = 'Search new keys without saved translations';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $translationKeys = $this->findProjectTranslationsKeys();

        $locales = [
            TranslationSide::ADMIN => array_keys(config('cms.core.translations.admin.locales', [])),
            TranslationSide::SITE => array_keys(app('locales')),
        ];

        $newKeys = [];
        foreach ($translationKeys as $key) {
            $parsedKey = Translation::parseKey($key);
            $side = array_get($parsedKey, 'side');

            if (!TranslationSide::hasValue($side)) {
                $this->error("Unsupported side '{$side}'. Key: '{$key}'");
                continue;
            }

            foreach ($locales[$side] as $locale) {
                if (!$this->storage->hasSavedKey($parsedKey['namespace'], $side, $parsedKey['key'], $locale)) {
                    $this->warn("Missing key '{$key}', locale '{$locale}'");
                    $newKeys[] = $key;
                }
            }
        }

        if ($newKeys) {
            $this->info('Found: ' . count($newKeys) . ' keys!');
        } else {
            $this->info('New keys not found!');
        }
        $this->line('');
    }
}
