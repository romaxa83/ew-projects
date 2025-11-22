<?php

namespace App\Imports\Spares\Strategies;

use App\Imports\Spares\Imports\VolvoImport;
use App\Services\Telegram\TelegramDev;
use Maatwebsite\Excel\Facades\Excel;

class VolvoStrategy extends AbstractStrategy
{
    public function import(string $pathToFile)
    {
        $callStartTime = microtime(true);

        Excel::import(new VolvoImport(), $pathToFile);

        $time = number_format($time_elapsed_secs = microtime(true) - $callStartTime,2 ,'.','');
        $str = "Memory peak: ".(memory_get_peak_usage(true)/1024/1024) . " MiB" . PHP_EOL . "Memory usage: ".(memory_get_usage(true)/1024/1024) . " MiB" . PHP_EOL . "Time: ".$time;
        // @todo dev-telegram
        TelegramDev::info($str);
    }
}
