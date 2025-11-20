<?php

namespace App\Console\Commands\Workers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ClearLogFile extends Command
{
    protected $signature = 'workers:clear_log_file';

    protected $description = 'Чистит файлы логов';

    protected array $paths = [];
    protected $date;

    public function __construct()
    {
        $this->paths = [
            storage_path('logs/info-laravel.log'),
            storage_path('logs/ari.log'),
//            storage_path('logs/laravel.log'),
        ];

        // удаляем записи которым больше n часов
        $this->date = CarbonImmutable::now()->subHours(config('logging.channels.eyes.hours_as_old'));

        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            foreach ($this->paths as $path){
                $rows = file($path);
                $count = 0;
                foreach (array_chunk($rows, 200, true) as $data){
                    foreach ($data as $k => $row){
                        $dateStr = $this->parseDate($row);
                        $dateRow = null;
                        if($dateStr && Carbon::canBeCreatedFromFormat($dateStr, 'Y-m-d H:i:s')){
                            $dateRow = CarbonImmutable::parse($dateStr);
                        } else {
                            $count++;
                            unset($rows[$k]);
                        }

                        if($dateRow && $this->date->gt($dateRow)){
                            $count++;
                            unset($rows[$k]);
                        }
                    }
                }

                logger_info("[worker] CLEAR log file [{$path}], remove rows - {$count}");

                file_put_contents($path, implode("", $rows));

                sleep(2);
            }

            $time = microtime(true) - $start;
            $this->info($time);
            logger_info("[worker] REMOVE old resc from logs file [time = {$time}]");
        } catch (\Exception $e){
            logger_info("[worker] REMOVE old resc from logs file FAIL", [$e]);
            $this->error($e->getMessage(), []);
        }
    }

    public static function parseDate(string $value): ?string
    {
        preg_match("/(?<=\[).+?(?=\])/", $value, $res);
        return $res[0] ?? null;
    }
}
