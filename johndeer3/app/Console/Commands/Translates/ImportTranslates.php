<?php

namespace App\Console\Commands\Translates;

use App\Models\Translate;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportTranslates extends Command
{
    /** @var \Illuminate\Contracts\Foundation\Application */
    protected $app;
    /** @var \Illuminate\Filesystem\Filesystem */
    protected $files;

    protected $excludeFile = ['auth', 'message','passwords', 'translates', 'validation'];

    protected $signature = 'jd:import-translates';

    protected $description = 'Importing translations from a file into a database';

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
        // @see https://github.com/barryvdh/laravel-translation-manager/blob/master/src/Console/ImportCommand.php

        $this->info('Перегоняем перевод из файлов в бд');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $base = $this->app['path.lang'];
        foreach ($this->files->directories($base) as $langPath){
            $locale = basename($langPath);//en
            foreach ($this->files->allfiles($langPath) as $file){

                $info = pathinfo($file);
                $group = $info['filename'];
                if(!Translate::checkExportGroup($group)) {
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
                    foreach (\Arr::dot($translations) as $key => $value) {

                        $key = $group .'::'. $key;

                        if(!$this->checkExist($group, $key, $locale)){
                            $t = new Translate();
                            $t->model = Translate::TYPE_SITE;
                            $t->group = $group;
                            $t->alias = $key;
                            $t->text = $value;
                            $t->lang = $locale;
                            $t->save();

                            $progressBar->advance();
                        }
                    }
                }
            }
        }
        $progressBar->finish();
        $this->info(PHP_EOL);
    }

    private function checkExist($group, $alias, $lang): bool
    {
        return Translate::query()
            ->where('group', $group)
            ->where('alias', $alias)
            ->where('lang', $lang)
            ->exists();
    }
}

