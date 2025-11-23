<?php

namespace App\Console\Commands\Init;

use Illuminate\Console\Command;
use WezomCms\Firebase\Helpers\SetTemplates;

class SetTemplate extends Command
{
    protected $signature = 'cmd:set-template';

    protected $description = 'Устанавливает шаблоны для уведомлений';

    public function handle()
    {
        app(SetTemplates::class)->run();
    }
}
