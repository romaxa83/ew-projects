<?php

namespace App\Console\Commands\Workers;

use App\Services\Musics\MusicService;
use Illuminate\Console\Command;

class UnholdMusic extends Command
{
    protected $signature = 'workers:unhold_music';

    protected $description = 'После рабочего дня восстанавливаем данные по музыке';

    public function __construct(
        protected MusicService $service,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        try {
            $start = microtime(true);

            $this->service->unholdMusicToEndWorkDay();

            $time = microtime(true) - $start;
            logger_info("[worker] UNHOLD MUSIC [{$time}]");
        } catch (\Exception $e){
            logger_info("[worker] UNHOLD MUSIC", [$e]);
            $this->error($e->getMessage(), []);
        }
    }
}


