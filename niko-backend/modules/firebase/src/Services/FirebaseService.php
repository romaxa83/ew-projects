<?php

namespace WezomCms\Firebase\Services;

use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\TelegramBot\Telegram;

class FirebaseService
{
    private $url;
    private $from_token;

    public function __construct()
    {
        $this->url = config('cms.firebase.firebase.fcm_send_url');
        $this->from_token = config('cms.firebase.firebase.firebase_server_key');
    }

    // @todo когда у пользователя появиться fcm_token, обработать правильный результат
    public function send($toToken, $title, $body, $fmcNotification = null)
    {
        try {

            $fields = [
                'to' => $toToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ]
            ];

            $headers = [
                'Authorization: key=' . $this->from_token,
                'Content-Type: application/json'
            ];

            Telegram::event('Отправка данных на fcm - ' . serialize($fields));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

            $result = json_decode(curl_exec($ch));

            curl_close($ch);

            if($result->success === 0){
                if($fmcNotification){
                    /** @var $fmcNotification FcmNotification */
                    $fmcNotification->setError($result);
                }

                logger(serialize($result));
                $message = 'Fcm error';
                $message .= isset($result->results[0]->error) ? ' - ' . $result->results[0]->error : '';
                throw new \Exception($message);
            }

            return $result;

        } catch (\Exception $exception){

            Telegram::event('Ошибка запроса fcm');
            Telegram::event($exception->getMessage());


//            throw new \Exception($exception->getMessage());
        }
    }
}
