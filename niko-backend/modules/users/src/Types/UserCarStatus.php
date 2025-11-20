<?php

namespace WezomCms\Users\Types;

// внутреннии статусы по автомобилю пользователя
final class UserCarStatus
{
    const ACTIVE     = 0;
    const DELETED    = 1; // машина удалена
    const FROM_ORDER = 2; // машина из заявки (не являеться частью гаража)

    private function __construct(){}
}
