<?php

namespace WezomCms\Firebase\Types;

final class FcmNotificationStatus
{
    const CREATED    = 1;   // создана
    const SEND       = 2;   // отправлена в firebase
    const HAS_ERROR  = 3;   // получена ошибка при отправки в firebase

    private function __construct(){}
}

