<?php

namespace WezomCms\Firebase\Listeners;

use WezomCms\Firebase\Events\FcmNotificationEvent;
use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\Firebase\Services\FirebaseService;
use WezomCms\TelegramBot\Telegram;

class FcmNotificationListener
{
    /**
     * @param FcmNotificationEvent $event
     * @throws \Exception
     */
    public function handle(FcmNotificationEvent $event)
    {
        if(config('cms.firebase.firebase.firebase_use')){

            $fcmNotification = FcmNotification::create($event->getUser()->id, $event->type, $event->data, $event->orderId);

            if(!$event->getUser()->fcm_token){
                $message = 'User ('. $event->getUser()->id .') not have fcm_token';
                logger($message);

                $fcmNotification->setError($message);
//                throw new \Exception($message);

                return null;
            }

            $fcm = \App::make(FirebaseService::class);
            $result = $fcm->send($event->getUser()->fcm_token, $event->getTitleMsg(), $event->getBodyMsg(), $fcmNotification);

            Telegram::event('ОТВЕТ ОТ FCM ' . serialize($result));

            $fcmNotification->setSuccessData($result);
        }
    }
}
