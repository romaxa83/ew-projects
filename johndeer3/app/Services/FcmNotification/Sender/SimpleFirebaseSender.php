<?php

namespace App\Services\FcmNotification\Sender;

use App\Helpers\Logger\FcmLogger;
use App\Models\Notification\FcmNotification;
use App\Services\FcmNotification\Exception\FcmNotificationException;
use App\Services\FcmNotification\FcmNotyItemPayload;
use App\Services\Telegram\TelegramDev;

class SimpleFirebaseSender implements FirebaseSender
{
    private $url;
    private $serverKey;

    public function __construct(string $url, string $serverKey)
    {
        $this->url = $url;
        $this->serverKey = $serverKey;
    }

    public function send(FcmNotyItemPayload $data, FcmNotification $fcm = null)
    {
        try {
            $fields = [
                'to' => $data->getFcmToken(),
                'notification' => [
                    'title' => $data->getMessagePayload()->getTitle(),
                    'body' => $data->getMessagePayload()->getText(),
                ]
            ];

            $headers = [
                'Authorization: key=' . $this->serverKey,
                'Content-Type: application/json'
            ];

            FcmLogger::INFO("Отправка данных на fcm", $fields);
//            TelegramDev::info('Отправка данных на fcm - ' . serialize($fields));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 500);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            $result = json_decode(curl_exec($ch));

            if($result->success === 0){
                logger(serialize($result));
                $message = 'Fcm error';
                $message .= isset($result->results[0]->error) ? ' - ' . $result->results[0]->error : '';

                if($fcm){
                    /** @var $fcm FcmNotification */
                    $fcm->setError($message);
                }
                throw new FcmNotificationException($message);
            }

            return $result;

        } catch (FcmNotificationException $exception){
            FcmLogger::ERROR("Ошибка запроса fcm [{$data->getUserId()}]");
//            TelegramDev::warn('Ошибка запроса fcm', "[{$data->getUserId()}]", __FILE__);
//            TelegramDev::error(__FILE__, $exception);
            if($fcm){
                /** @var $fcm FcmNotification */
                $fcm->setError($exception->getMessage());
            }
        }
    }
}


