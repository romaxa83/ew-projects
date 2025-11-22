<?php

namespace App\Listeners\SmsVerify;

use App\Events\SmsVerify\SendSmsCode;
use App\Services\Sms\Sender\SmsSender;
use App\Services\Telegram\TelegramDev;

class SendSmsCodeListeners
{
    public function __construct(private SmsSender $sender)
    {}

    public function handle(SendSmsCode $event)
    {
        try {
            if(config('sms.enable_sender')){

                $this->sender->send(
                    $event->smsVerify->phone,
                    __('message.user.sms code', ['code' => $event->smsVerify->sms_code]) . ' - ' . $event->smsVerify->sms_code
                );

                TelegramDev::info("На sms-sender отправлен код - {$event->smsVerify->sms_code} \n для телефона  {$event->smsVerify->phone} \n driver - " .config('sms.driver'), null, TelegramDev::LEVEL_IMPORTANT);
            }

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
