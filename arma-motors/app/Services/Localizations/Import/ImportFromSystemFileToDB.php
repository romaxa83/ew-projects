<?php

namespace App\Services\Localizations\Import;

use App\Models\Localization\Translation;
use App\Services\Localizations\LocalizationService;
use App\Services\Localizations\TranslationService;
use App\Services\Telegram\TelegramDev;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

class ImportFromSystemFileToDB implements ImportTranslation
{
    protected $baseLang = 'en';

    protected $config;

    public function __construct(
        protected Application $app,
        protected Filesystem $files,
        protected LocalizationService $localizationService,
        protected TranslationService $translationService,
    )
    {
        $this->config = config('translates');
    }

    public function handle(): void
    {

        $base = $this->app['path.lang'];
//        $langs = $this->localizationService->getLocalesAsArray(); // ['ru', 'uk']

        foreach ($this->files->directories($base) as $langPath){

            $count = 0;
            $group = null;
            $locale = basename($langPath); //en
//            if(!in_array($locale, $langs)){
//                continue;
//            }

            if($locale == $this->baseLang){
                foreach ($this->files->allfiles($langPath) as $file){
                    $info = pathinfo($file);
                    $group = $info['filename'];

                    if(!$this->checkGroup($group)){
                        continue;
                    }
                    $subLangPath = str_replace($langPath.DIRECTORY_SEPARATOR, '', $info['dirname']);
                    $subLangPath = str_replace(DIRECTORY_SEPARATOR, '/', $subLangPath);
                    $langPath = str_replace(DIRECTORY_SEPARATOR, '/', $langPath);
                    if ($subLangPath != $langPath) {
                        $group = $subLangPath.'/'.$group;
                    }
                    $translations = \Lang::getLoader()->load($locale, $group);

                    if ($translations && is_array($translations)) {
                        // @todo так себе решение, подумать на досуге
                        $l = $locale == 'en' ? 'ru' : $locale;
                        foreach (\Arr::dot($translations) as $key => $value) {
                            if(is_array($value)){
                                continue;
                            }

                            $this->translationService->createOrUpdateOneRow(
                                Translation::PLACE_SYSTEM,
                                $l,
                                $key,
                                $value,
                                $group,
                                false
                            );
                            $count++;
                        }
                    }
//                    dd('2');
                }

            }

        }
    }

    private function checkGroup($group): bool
    {
        return in_array($group, \Arr::get($this->config, 'import.group'));
    }
}
