<?php

namespace App\Services\Localizations\Export;

use App\Repositories\Localization\TranslationRepository;
use App\Services\Localizations\LocalizationService;
use App\Services\Localizations\TranslationService;
use App\Services\Telegram\TelegramDev;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

class ExportFromDBToSystemFile implements ExportTranslation
{
    protected $config;
    protected $basePath;

    public function __construct(
        protected Application $app,
        protected Filesystem $files,
        protected LocalizationService $localizationService,
        protected TranslationService $translationService,
        protected TranslationRepository $translationRepository,
    )
    {
        $this->config = config('translates');
        $this->basePath = $this->app['path.lang'];
    }

    public function handle(): void
    {
        foreach (\Arr::get($this->config, 'export.group') as $group){
            $this->exportByGroup($group);
        }
    }

    public function exportByGroup($group)
    {
        $tree = $this->makeTree(
            $this->translationRepository->getByGroup($group)
        );

        foreach ($tree as $lang => $groups){

            if(isset($groups[$group])){
                $translations = $groups[$group];
                $locale_path = $lang . DIRECTORY_SEPARATOR . $group;

                $subfolders = explode(DIRECTORY_SEPARATOR, $locale_path);
                array_pop($subfolders);
                $subfolder_level = '';

                foreach ($subfolders as $subfolder) {
                    $subfolder_level = $subfolder_level.$subfolder.DIRECTORY_SEPARATOR;

                    $temp_path = rtrim($this->basePath . DIRECTORY_SEPARATOR . $subfolder_level, DIRECTORY_SEPARATOR);
                    if (! is_dir($temp_path)) {
                        mkdir($temp_path, 0777, true);
                    }
                }

                $path = $this->basePath . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $group . '.php';

                $output = "<?php\n\nreturn " . var_export($translations, true).';'.\PHP_EOL;

                $this->files->put($path, $output);

//                TelegramDev::info("📥 Для группы {$group} перезаписан файл ({$lang})");
            }
        }
    }

    protected function makeTree($translations)
    {
        $array = [];
        foreach ($translations as $translation) {
            \Arr::set($array[$translation->lang][$translation->group], $translation->key,
                $translation->text);
        }

        return $array;
    }
}

