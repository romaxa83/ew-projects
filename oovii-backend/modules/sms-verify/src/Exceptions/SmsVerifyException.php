<?php

namespace WezomCms\SmsVerify\Exceptions;

use Exception;
use Illuminate\Http\Response;

class SmsVerifyException extends Exception
{
    // запрос на генерацию action токена, но он еще активен
    public static function throwActiveActionToken(): void
    {
        throw new self(
            __('cms-sms-verify::site.exception.action token active'),
            Response::HTTP_BAD_REQUEST
        );
    }

    // не найдена запись по actionToken
    public static function throwNotFoundActionToken($token = null): void
    {
        throw new self(
            __('cms-sms-verify::site.exception.not found action token', [
                'token' => $token
            ]),
            Response::HTTP_BAD_REQUEST
        );
    }

    // actionToken протух
    public static function throwExpiredActionToken($token = null): void
    {
        throw new self(
            __('cms-sms-verify::site.exception.expired action token', [
                'token' => $token
            ]),
            Response::HTTP_BAD_REQUEST
        );
    }

    // запрос на генерацию sms токена, но он еще активен
    public static function throwActiveSmsToken(): void
    {
        throw new self(
            __('cms-sms-verify::site.exception.sms token active'),
            Response::HTTP_BAD_REQUEST
        );
    }

    // не найдена запись по smsToken
    public static function throwNotFoundSmsToken($token = null): void
    {
        throw new self(
            __('cms-sms-verify::site.exception.not found sms token', [
                'token' => $token
            ]),
            Response::HTTP_BAD_REQUEST
        );
    }

    // smsToken протух
    public static function throwExpiredSmsToken($token = null): void
    {
        throw new self(
            __('cms-sms-verify::site.exception.expired sms token', [
                'token' => $token
            ]),
            Response::HTTP_BAD_REQUEST
        );
    }

    // smsToken протух
    public static function throwNotEqualSmsCode(): void
    {
        throw new self(
            __('cms-sms-verify::site.exception.not equals sms code'),
            Response::HTTP_BAD_REQUEST
        );
    }
}
