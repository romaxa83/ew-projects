<?php

namespace App\Enums\Common;

use App\Enums\Base\InvokableCases;

/**
 * @method static string Request()
 * @method static string Api()
 * @method static string SendEmail()
 * @method static string Websocket()
 * @method static string Webhook()
 * @method static string ComRec()
 * @method static string SyncRingostat()
 */
enum LogKeyEnum: string {

    use InvokableCases;

    case Request = "[request]";
    case Api = "[api]";
    case SendEmail = "[send-email] ";
    case Websocket = "[websocket] ";
    case Webhook = "[webhook] ";
    case ComRec = "[com-rec] ";
    case SyncRingostat = "[sync-ringostat] ";
}

