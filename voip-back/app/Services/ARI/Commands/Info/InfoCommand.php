<?php

namespace App\Services\ARI\Commands\Info;

use App\Services\ARI\Commands\BaseCommand;

class InfoCommand extends BaseCommand
{
    public function getUri(): string
    {
        return 'ari/asterisk/info';
    }
}

