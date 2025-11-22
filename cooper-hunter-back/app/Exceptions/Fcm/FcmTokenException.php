<?php


namespace App\Exceptions\Fcm;


use Core\Exceptions\TranslatedException;

class FcmTokenException extends TranslatedException
{

    public function __construct()
    {
        parent::__construct(trans('validation.custom.fcm_token_invalid'));
    }
}
