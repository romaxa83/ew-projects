<?php

namespace App\Console\Commands\Worker;

use App\Repositories\Sms\SmsVerifyRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class RemoveOldSmsToken extends Command
{
    protected $signature = 'am:worker:remove-sms-token';

    protected $description = 'Удаляет старые sms токены';

    public function handle(SmsVerifyRepository $repository)
    {
        $days = config('sms.verify.old_days');

        $models = $repository->getForRemove($days);

        TelegramDev::info("🗑 Remove [{$models->count()}] SMS tokens", 'SYSTEM',TelegramDev::LEVEL_IMPORTANT);

        $models->each(function(Model $m){
            $m->forceDelete();
        });
    }
}
