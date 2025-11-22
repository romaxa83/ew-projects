<?php

namespace App\Console\Commands\ParserHelpers;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ParserRenameFilesCommand extends Command
{
    private const DIR = 'tests/Data/Files/Pdf/';

    protected $signature = 'parser:rename-files';
    protected $description = 'Rename dispatcher files in test folder';

    public function handle(): int
    {
        $dir = base_path(self::DIR);
        $contents = scandir($dir);
        unset($contents[0], $contents[1]);
        $dirs = [];
        foreach ($contents as $item) {
            if (!is_dir($dir . $item)) {
                continue;
            }
            $dirs[] = $item;
        }
        $dispatcher = $this->choice(
            "Chose dispatcher type",
            $dirs
        );
        $dir = base_path(self::DIR . $dispatcher);

        $files = scandir($dir);
        unset($files[0], $files[1]);
        $files = array_values($files);

        if (empty($files)) {
            $this->warn(sprintf("Folder [%] is empty", $dir));
            return self::FAILURE;
        }

        $filePrefix = Str::snake($dispatcher) . '_';
        for ($i = 0, $max = count($files); $i < $max; $i++) {
            $newFileName = $filePrefix . ($i + 1) . '.pdf';
            rename($dir . '/' . $files[$i], $dir . '/' . $newFileName);
            $this->info(sprintf("Renamed [%s] to [%s]", $files[$i], $newFileName));
        }
        return self::SUCCESS;
    }
}
