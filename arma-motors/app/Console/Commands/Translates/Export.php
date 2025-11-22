<?php

namespace App\Console\Commands\Translates;

use App\Services\Localizations\Export\ExportFromDBToSystemFile;
use Illuminate\Console\Command;

class Export extends Command
{
    protected $signature = 'am:translates-export';

    protected $description = 'Exporting translations from a db into a file';

    public function __construct(protected ExportFromDBToSystemFile $import)
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->info('Перегоняем перевод из бд в файлы');

            $this->import->handle();

            $this->info('Done');
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
}
