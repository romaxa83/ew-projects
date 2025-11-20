<?php

namespace WezomCms\Core\Api;

class ErrorCode
{
    const UNKNOWN = 0;
    const NOT_VALID_ACCESS_TOKEN = 1;
    const USER_NOT_FOUND = 3;
    const SMS_TOKEN_EXPIRED = 2;
    const SMS_TOKEN_INCORRECT = 1;

    const DEVICE_ID_INCORRECT = 4;
    const HAS_ACTIVE_SESSION = 2;
}
