<?php

use WezomCms\SmsVerify\Http\Controllers\Api\V1\SmsVerifyController;

Route::post('mobile/sms-verify/verify', [SmsVerifyController::class, 'verify'])
    ->name('api.v1.mobile.sms-verify');

Route::post('mobile/sms-verify/check', [SmsVerifyController::class, 'check'])
    ->name('api.v1.mobile.sms-check');
