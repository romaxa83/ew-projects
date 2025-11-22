<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;

class Import extends Command
{
    protected $signature = 'import:data';

    protected $description = 'Импортирует данные по моделям и брендов в файлы';

    public function handle()
    {
        $this->call('import:brand');
        $this->call('import:model');
    }
}
