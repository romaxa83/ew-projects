<?php

namespace App\Console\Commands\Worker;

use App\Repositories\Sms\SmsVerifyRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class RemoveOldAAResponse extends Command
{
    protected $signature = 'am:worker:remove-aa-responses';

    protected $description = 'Удаляет старые записи запросов по синхронизации аа';

    public function handle(SmsVerifyRepository $repository)
    {
        $days = config('aa.old_days');

        $models = $repository->getForRemove($days);

        TelegramDev::info("🗑 Remove [{$models->count()}] AA Responses", 'SYSTEM',TelegramDev::LEVEL_IMPORTANT);

        $models->each(function(Model $m){
            $m->forceDelete();
        });
    }
}
