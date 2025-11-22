<?php

namespace App\Console\Commands\Worker;

use App\Repositories\Email\EmailVerifyRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class RemoveOldEmailToken extends Command
{
    protected $signature = 'am:worker:remove-email-token';

    protected $description = 'Удаляет старые email токены';

    public function handle(EmailVerifyRepository $repository)
    {
        $days = config('user.verify_email.old_days');
        $models = $repository->getForRemove($days);

        TelegramDev::info("🗑 Remove [{$models->count()}] email tokens", 'SYSTEM',TelegramDev::LEVEL_IMPORTANT);

        $models->each(function(Model $m){
            $m->forceDelete();
        });
    }
}

