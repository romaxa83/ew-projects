<?php

namespace App\Console\Commands\Test;

use App\Jobs\SendMobileAppDocumentToTelegram;
use App\Models\Attachment;
use Illuminate\Console\Command;

class TelegramCommand extends Command
{

    protected $signature = 'test:telegram_send {--id=}';

    public function handle():int
    {
        $id = (int)$this->option('id');
        if(is_null($id)){
            $id = 14784;
        }

        $attach = Attachment::query()
            ->whereJsonContains('miscs->object->id', $id)
            ->first();

        if(!$attach){
            $this->warn("Not found attachment by order_id [{$id}]");;
            return self::FAILURE;
        }

        /** @var $service SendMobileAppDocumentToTelegram */
        SendMobileAppDocumentToTelegram::dispatch($attach->id, 'bol');

        return self::SUCCESS;
    }
}
