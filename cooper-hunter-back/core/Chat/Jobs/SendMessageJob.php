<?php

namespace Core\Chat\Jobs;

use Core\Chat\Models\Message;
use Core\Chat\Services\MessageNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Message $message)
    {
    }

    public function handle(MessageNotificationService $service): void
    {
        $service->createMessageNotifications($this->message);
    }
}
