<?php

namespace WezomCms\SmsVerify\Listeners;

use WezomCms\SmsVerify\Events\SendSmsCode;
use WezomCms\SmsVerify\Services\Sms\SmsSender;
use WezomCms\TelegramBot\Telegram;

class SendSmsCodeListeners
{
    public function __construct(private SmsSender $sender)
    {}

    public function handle(SendSmsCode $event)
    {
        try {
            if(config('cms.sms-verify.config.sender.enable')){
                $this->sender->send(
                    $event->smsVerify->phone,
                    __('cms-sms-verify::site.messages.sms_code', ['code' => $event->smsVerify->code])
                );

                Telegram::info("📲 Send SMS code [{$event->smsVerify->code}]", $event->smsVerify->phone, Telegram::LEVEL_IMPORTANT);
            }
        } catch (\Throwable $e) {
            Telegram::error($e, $event->smsVerify->phone);
            \Log::error($e->getMessage());
        }
    }
}

