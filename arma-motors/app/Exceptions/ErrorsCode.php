<?php

namespace App\Exceptions;

class ErrorsCode
{
    public const BAD_REQUEST = 400;
    public const NOT_FOUND = 404;
    public const NOT_AUTH = 401;
    public const NOT_PERM = 403;

    // Email verify
    // токен еще активен
    public const ACTIVE_EMAIL_VERIFY_TOKEN = 11;
    // токен на верификацию протух, нужен новый запрос
    public const EMAIL_TOKEN_EXPIRED  = 12;
    // нет записи в бд по данному токену
    public const EMAIL_TOKEN_NOT_FOUND_RECORD = 13;
    // не валиден accessToken
    public const ACCESS_TOKEN_NOT_VALID = 21;
    // не найдена запись по данному accessToken
    public const ACCESS_TOKEN_NOT_FOUND_RECORD = 22;
    // SmsVerify
    // не переданные нужные поля для начала верификации
    public const SMS_VERIFY_NOT_FIELD = 31;
    // время жизни actionToken еще не истекло
    public const ACTION_TOKEN_ACTIVE = 32;
    // время жизни smsToken еще не истекло
    public const SMS_TOKEN_ACTIVE = 33;
    // время жизни smsToken истекло
    public const SMS_TOKEN_EXPIRED = 34;
    // время жизни actionToken истекло
    public const ACTION_TOKEN_EXPIRED = 35;
    // не корректный sms код
    public const SMS_CODE_WRONG = 36;
    // не найдена запись по данному smsToke
    public const SMS_TOKEN_NOT_FOUND_RECORD = 37;
    // не найдена запись по данному actionToke
    public const ACTION_TOKEN_NOT_FOUND_RECORD = 38;
    // MobileToken
    // отсутствует refreshToken в payload
    public const MOBILE_TOKEN_INCORRECT_REFRESH_TOKEN = 51;
    // отсутствует deviceId в payload
    public const MOBILE_TOKEN_INCORRECT_DEVICE_ID = 52;
    // не совпадает deviceId
    public const MOBILE_TOKEN_NOT_EQUALS_DEVICE_ID = 53;
    // проблема с генерацией нового refreshToken
    public const MOBILE_TOKEN_PROBLEM_WITH_GENERATE_REFRESH_TOKEN = 54;
    // не понятная проблема
    public const MOBILE_TOKEN_SOMETHING_WRONG = 55;

    // проблема с импортом данных
    public const IMPORT_PROBLEM = 61;

    // новый телефон идентичен старому
    public const EDIT_PHONE_EQUALS_OLD = 41;
    // данный телефон используется другим пользователем
    public const EDIT_PHONE_EXIST = 42;

    // есть активная сессия на другом девайсе
    public const LOGIN_HAS_ACTIVE_SESSION = 21;
    // не верифицирован номер
    public const LOGIN_PHONE_NOT_VERIFY = 22;
    // неверный телефон или пароль
    public const LOGIN_WRONG_CREDENTIALS = 23;
    public const AUTH_UNDEFINED_ERROR = 24;
}
