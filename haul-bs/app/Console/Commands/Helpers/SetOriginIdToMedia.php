<?php

namespace App\Console\Commands\Helpers;

use App\Foundations\Modules\Media\Models\Media;
use Illuminate\Console\Command;

class SetOriginIdToMedia extends Command
{
    protected $signature = 'helpers:media_set_origin_id';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec()
    {
        $media = Media::query()->get();
        foreach ($media as $item){
            $item->origin_id = $item->id;
            $item->save();
        }
    }
}

