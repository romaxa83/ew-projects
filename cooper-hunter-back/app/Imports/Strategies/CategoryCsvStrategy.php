<?php

namespace App\Imports\Strategies;

use App\Imports\AbstractStrategy;
use App\Imports\Imports\CategoryCsvImport;
use Exception;
use Throwable;

class CategoryCsvStrategy extends AbstractStrategy
{
    /**
     * @throws Throwable
     */
    public function import(string $pathToFile): void
    {
        if (!file_exists($pathToFile)) {
            throw new Exception("File [$pathToFile] not exist");
        }

        $callStartTime = microtime(true);

        app(CategoryCsvImport::class)->run($pathToFile);

        $time = number_format($time_elapsed_secs = microtime(true) - $callStartTime, 2, '.', '');
        $str = "Memory peak: " . (memory_get_peak_usage(
                    true
                ) / 1024 / 1024) . " MiB" . PHP_EOL . "Memory usage: " . (memory_get_usage(
                    true
                ) / 1024 / 1024) . " MiB" . PHP_EOL . "Time: " . $time;

        logger($str);
    }
}
