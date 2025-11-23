<?php

namespace WezomCms\SmsVerify;

use InvalidArgumentException;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\SmsVerify\Events\SendSmsCode;
use WezomCms\SmsVerify\Listeners\SendSmsCodeListeners;
use WezomCms\SmsVerify\Services\Sms\ArraySender;
use WezomCms\SmsVerify\Services\Sms\KazinfotehSender;
use WezomCms\SmsVerify\Services\Sms\SmsSender;
use Illuminate\Contracts\Foundation\Application;

class SmsVerifyServiceProvider extends BaseServiceProvider
{

    protected $listen = [
        SendSmsCode::class => [
            SendSmsCodeListeners::class,
        ],
    ];

    public function register(): void
    {
        $this->app->singleton(SmsSender::class, function (Application $app) {
            $config = config('cms.sms-verify.config.sender');

            return match ($config['driver']) {
                'kazinfoteh' => new KazinfotehSender(
                    $config['drivers']['kazinfoteh']['url'],
                    settings('users.sms_service.kazinfoteh_login') ?? $config['drivers']['kazinfoteh']['login'],
                    settings('users.sms_service.kazinfoteh_password') ?? $config['drivers']['kazinfoteh']['password']
                ),
                'array' => new ArraySender(),
                default => throw new InvalidArgumentException('Undefined SMS driver ' . $config['driver']),
            };
        });
    }

    public function boot(): void
    {
        parent::boot();
    }
}

