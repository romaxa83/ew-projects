<?php

namespace App\Console\Commands\Translates;

use App\Services\Localizations\Import\ImportTranslation;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class Import extends Command
{
    protected $signature = 'am:translates-import';

    protected $description = 'Importing translations from a file into a database';

    public function __construct(protected ImportTranslation $import)
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->info('Перегоняем перевод из файлов в бд');

            $this->import->handle();

            $this->info(PHP_EOL);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }
}
