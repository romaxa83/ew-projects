<?php

namespace App\Console\Commands\Worker;

use App\Repositories\AA\AAPostScheduleRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class RemoveOldAAFreeSlot extends Command
{
    protected $signature = 'am:worker:remove-aa-free-slots';

    protected $description = 'Удаляет старые слоты времени аа';

    public function handle(AAPostScheduleRepository $repository)
    {
        $models = $repository->getForRemove();

        TelegramDev::info("🗑 Remove [{$models->count()}] AA Free Slot Schedule", 'SYSTEM',TelegramDev::LEVEL_IMPORTANT);

        $models->each(function(Model $m){
            $m->forceDelete();
        });
    }
}

