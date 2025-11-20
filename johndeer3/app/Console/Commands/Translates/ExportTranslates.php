<?php

namespace App\Console\Commands\Translates;

use App\Models\Translate;
use App\Services\Translations\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;

class ExportTranslates extends Command
{
    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $app;
    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    protected $excludeFile = ['auth', 'message','passwords', 'translates', 'validation'];

    protected $signature = 'jd:export-translates';

    protected $config;

    protected $description = 'Importing translations from a databases into a file';

    public function __construct(Application $app, Filesystem $files)
    {
        parent::__construct();

        $this->app = $app;
        $this->files = $files;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        // @see https://github.com/barryvdh/laravel-translation-manager/blob/master/src/Manager.php

        $this->info('Перегоняем перевод из бд в файлы');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $basePath = $this->app['path.lang'];

        foreach (Translate::exportGroup() as $group){
            $tree = $this->makeTree(
                Translate::ofTranslatedGroup($group)
                    ->orderByGroupKeys(false)
                    ->get()
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

                        $temp_path = rtrim($basePath . DIRECTORY_SEPARATOR . $subfolder_level, DIRECTORY_SEPARATOR);
                        if (! is_dir($temp_path)) {
                            mkdir($temp_path, 0777, true);
                        }
                    }

                    $path = $basePath . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $group . '.php';

                    $output = "<?php\n\nreturn " . var_export($translations, true).';'.\PHP_EOL;

                    $this->files->put($path, $output);
                    $progressBar->advance();
                }
            }
        }

        $progressBar->finish();
        $this->info(PHP_EOL);
    }

    protected function makeTree($translations)
    {
        $array = [];
        foreach ($translations as $translation) {
            $alias = last(explode('::', $translation->alias));
            \Arr::set($array[$translation->lang][$translation->group], $alias,
                $translation->text);
        }

        return $array;
    }
}

