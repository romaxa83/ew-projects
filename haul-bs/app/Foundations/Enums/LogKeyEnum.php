<?php

namespace App\Foundations\Enums;

use App\Foundations\Enums\Traits\InvokableCases;

/**
 * @method static string SendEmail()
 * @method static string SyncECom()
 */
enum LogKeyEnum: string {

    use InvokableCases;

    case SendEmail = "[send-email]";
    case SyncECom = "[sync-ecomm]";
    case Request = "[request]";
}
