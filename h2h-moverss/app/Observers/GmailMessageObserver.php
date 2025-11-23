<?php

namespace App\Observers;

use App\Models\Mailbox\Gmail\Message;
use App\Services\Communications\RecordCreateService;

class GmailMessageObserver
{
    public function created(Message $model)
    {
//        logger_gmail('GMAIL MESSAGE CREATED', [$model->toArray()]);
        RecordCreateService::handler($model);
    }

    public function updated(Message $model)
    {
//        logger_gmail('GMAIL MESSAGE UPDATED', [$model->toArray()]);
        RecordCreateService::updatedMessage($model);
    }
}
