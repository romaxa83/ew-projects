<?php

namespace App\Console\Commands\Translates;

use Illuminate\Console\Command;

class TranslationScan extends Command
{
    protected $signature = 'jd:translation-scan';

    protected $description = 'Run command for transfer translates from file to database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // базовые файлы на en

        // импортируем из файлов в бд
        $this->call('jd:import-translates');
        // копируем для все языков в бд
        $this->call('jd:copy-translates');
        // перегоняем обратно в файлы
        $this->call('jd:export-translates');
    }
}
