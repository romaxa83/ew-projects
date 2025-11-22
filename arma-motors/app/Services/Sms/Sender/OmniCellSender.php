<?php

namespace App\Services\Sms\Sender;

use App\Services\Telegram\TelegramDev;
use Illuminate\Support\Facades\Http;

// @see https://docs.omnicell.ua/pages/viewpage.action?pageId=4358420
class OmniCellSender implements SmsSender
{
    private $number;

    public function __construct(
        private $url,
        private $login,
        private $password,
    )
    {}

    private $messages = [];

    public function send($number, $text): void
    {
        $this->number = $number->getValue();

        $this->initMessage($number, $text);
        $this->request();
    }

    private function initMessage($number, $text)
    {
        $this->messages = [
            'id' => 'single',
            'validity' => '+30 min',
            'extended' => true,
            'source' => 'ARMA MOTORS',
            'desc' => 'Send sms code for verification',
            'type' => 'SMS',
            'to' => [
                [
                    'msisdn' => $number->getValue()
                ]
            ],
            'body' => [
                'value' => $text
            ]
        ];
    }

    private function request()
    {
        logger('SMS SEND', [
            'url' => $this->url,
            'message' => $this->messages
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode( $this->login . ':' . $this->password )
            ])
                ->withoutVerifying()
                ->post($this->url, $this->messages);

            logger('SMS RESPONSE', [
                'body' => $response->body(),
                'status' => $response->status(),
                'header' => $response->headers()
            ]);

            if($response->status() != 200 || $response->json('state.value') == 'canceled'){
                logger("SMS_SENDER_FAIL" . $response->body());

                throw new \Exception("Ошибка при отправке sms на номер {$this->number}");
            }

        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e);

            logger($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
