<?php

namespace App\Listeners\Firebase;

use App\Events\Firebase\FcmPush;
use App\Services\Firebase\FcmService;
use App\Services\Firebase\Sender\FirebaseSender;
use App\Services\Telegram\TelegramDev;
use App\Services\User\UserService;

class FcmPushListeners
{
    public function __construct(
        protected FirebaseSender $sender
    ){}

    public function handle(FcmPush $event)
    {
        if(config('firebase.enable_firebase')){
            try {
                $service = app(FcmService::class);
                $userService = app(UserService::class);
                $fcm = $service->createFromEvent($event);

                if(!$event->user->hasFcmToken()){
                    $message = 'User ('. $event->user->id .') not have fcm_token';
                    \Log::error($message);
                    $fcm->setError($message);

                    return null;
                }

                $result = $this->sender->send($event->user, $event->action, $fcm);

                $fcm->setSendStatus($result);
                $userService->changeHasNewNotifications($event->user, true);
            }
            catch (\Throwable $e) {
                \Log::error($e->getMessage());
                // @todo dev-telegram
                TelegramDev::error(__FILE__, $e, $event->user->name, TelegramDev::LEVEL_IMPORTANT);
            }
        }
    }
}
