<?php

namespace App\IPTelephony\Entities\Asterisk;
// @see https://voxlink.ru/kb/asterisk-configuration/asterisk-realtime-queue_log/
class QueueLogEntity
{
    public const TABLE = 'queue_log';

    public const CONNECT = 'CONNECT';                   // оператор ответил на звонок и разговаривает с абонентом
    public const COMPLETEAGENT = 'COMPLETEAGENT';       // вызов был отвечен. Оператор первым положил трубку
    public const COMPLETECALLER = 'COMPLETECALLER';     // вызов был отвечен. Абонент первым завершил звонок.
    public const ATTENDEDTRANSFER = 'ATTENDEDTRANSFER'; //  вызов был переведен с оповещением
    public const RINGNOANSWER = 'RINGNOANSWER';         // оператор не ответил на звонок при поступившем вызове
    public const BLINDTRANSFER = 'BLINDTRANSFER';       //  вызов был переведен без оповещения на другой экстеншн
    public const RINGCANCELED = 'RINGCANCELED';         //
    public const ENTERQUEUE = 'ENTERQUEUE';             // вызов поступил в очередь.
    public const ABANDON = 'ABANDON';                   // звонящий положил трубку, т.к. не дождался ответа
    public const EXITWITHTIMEOUT = 'EXITWITHTIMEOUT';   // вызов покинул очередь по истечению времени
    public const WITHDRAW = 'WITHDRAW';
    public const PAUSE = 'PAUSE';                       // указывается пауза в очереди
    public const UNPAUSE = 'UNPAUSE';                   // снятие с паузы оператором

    public const CALLID_NONE = 'NONE';
    public const CALLID_REALTIME = 'REALTIME';

    public static function answerCallStatuses(): array
    {
        return [
            self::COMPLETEAGENT,
            self::COMPLETECALLER,
            self::ATTENDEDTRANSFER,
        ];
    }

    public static function noAnswerCallStatuses(): array
    {
        return [
            self::RINGNOANSWER,
            self::BLINDTRANSFER,
            self::RINGCANCELED,
        ];
    }

    public static function withoutCallid(): array
    {
        return [
            self::CALLID_NONE,
            self::CALLID_REALTIME,
        ];
    }

    public static function eventForPause(): array
    {
        return [
            self::PAUSE,
            self::UNPAUSE,
        ];
    }
}
