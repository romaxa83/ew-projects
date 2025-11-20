<?php

namespace WezomCms\Users\Listeners;

use Daaner\TurboSMS\Facades\TurboSMS;
use WezomCms\TelegramBot\Events\TelegramDev;
use WezomCms\Users\Events\SmsCodeSend;

class SmsCodeSendListener
{
    /**
     * Handle the event.
     *
     * @param  SmsCodeSend  $event
     * @return void
     */
    public function handle(SmsCodeSend $event)
    {
        $message = __('cms-users::admin.sms_code_message', ['code' => $event->code]);
        event(new TelegramDev('код отправлен через sms'));

        $sms = TurboSMS::sendMessages($event->user->phone, $message);

        event(new TelegramDev('ответ - '. json_encode($sms, JSON_UNESCAPED_UNICODE)));
    }
}
