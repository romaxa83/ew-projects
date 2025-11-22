<?php

namespace App\Services\Firebase\Sender;

use App\Models\Notification\Fcm;
use App\Models\User\User;
use App\Services\Firebase\Exception\FcmNotificationException;
use App\Services\Firebase\FcmAction;
use App\Services\Telegram\TelegramDev;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class CustomFirebaseSender implements FirebaseSender
{
    public function __construct(
        public $url,
        public $serverKey
    )
    {}

    public function send(User $user, FcmAction $action, null|Fcm $fcm = null)
    {
        $lang = \App::getLocale();


        $fields = [
            'to' => $user->getFcmToken(),
            'notification' => [
                'title' => $action->getTitle(),
                'body' => $action->getBody(),
            ],
            'data' => [
                'type' => $action->getType(),
                'additional' => $action->getAdditional()
            ]
        ];
        logger("FCM-PUSH - [locale - {$lang}]", $fields);

        TelegramDev::info('Запрос на firebase' . PHP_EOL . serialize($fields), $user->name, TelegramDev::LEVEL_IMPORTANT);

        try {

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])
                ->withoutVerifying()
                ->post($this->url, $fields);

            if($response->json('success') === 0){
                if($fcm){
                    $fcm->setError($response->json());
                }
                throw new FcmNotificationException('Fcm response error - ' . $response->json('results.0.error'));
            }

            return $response->json();
        } catch (\Throwable $e){
            throw new FcmNotificationException($e->getMessage());
        }
    }
}

