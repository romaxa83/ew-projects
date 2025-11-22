<?php

namespace App\Console\Commands\Translates;

use Illuminate\Console\Command;

class Flow extends Command
{
    protected $signature = 'am:translates-flow';

    protected $description = 'Из lang/en записывает переводы в бд (которых нету), а затем из бд в файлы lang/ru,lang/uk';

    public function handle()
    {
        $this->call('am:translates-import');
        $this->call('am:translates-copy');
        $this->call('am:translates-export');

    }
}
