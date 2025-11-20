<?php

namespace App\Console\Commands\Translation;

use App\Services\Translations\TransferService;
use Illuminate\Console\Command;

class TranslationScan extends Command
{
    protected $signature = 'cmd:translations-scan';

    protected $description = 'Run command for transfer translates from file to database and back';

    private $service;

    public function __construct(TransferService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle()
    {
        $this->line("--[x] импортируем переводы из файлов в бд");
        $count = $this->service->fromFilesToDB();
        $this->info("-- кол-во [{$count}] записей в бд");

        $this->line("--[x] копируем записи для все языков в бд");
        $count = $this->service->copyRowForAllLang();
        $this->info("-- кол-во [{$count}] записей в бд");

        $this->line("--[x] из бд перегоняем переводы в файлы");
        $info = $this->service->fromDdToFiles();
        foreach ($info as $path => $count) {
            $this->info("-- {$path} - [{$count}]");
        }
    }
}
