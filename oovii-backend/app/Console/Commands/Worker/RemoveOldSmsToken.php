<?php

namespace App\Console\Commands\Worker;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use WezomCms\SmsVerify\Repositories\SmsVerifyRepository;
use WezomCms\TelegramBot\Telegram;

class RemoveOldSmsToken extends Command
{
    protected $signature = 'cmd:worker:remove-sms-token';

    protected $description = 'Удаляет старые sms токены';

    public function handle(SmsVerifyRepository $repository)
    {
        $days = config('sms.verify.old_days');

        $models = $repository->getForRemove($days);


        $models->each(function(Model $m){
            $m->forceDelete();
        });

        Telegram::info("🗑 Remove [{$models->count()}] SMS tokens", 'SYSTEM',Telegram::LEVEL_IMPORTANT);
    }
}
