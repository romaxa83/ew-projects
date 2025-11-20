<?php

namespace App\IPTelephony\Entities\Asterisk;

class CdrEntity
{
    const TYPE_DIAL   = 'Dial';
    const TYPE_QUEUE  = 'Queue';
    const TYPE_HANGUP = 'Hangup';
    const TYPE_BACKGROUND = 'BackGround';
    const TYPE_PLAYBACK = 'Playback';
    const STATUS_CRM_TRANSFER = 'CRM_TRANSFER';
    const STATUS_ANSWERED = 'ANSWERED';
    const STATUS_ANSWER = 'ANSWER';
    const STATUS_NO_ANSWER = 'NO ANSWER';
    const STATUS_TRANSFER = 'TRANSFER';
    const STATUS_CANCEL = 'CANCEL';

    public const TABLE = 'cdr';

    public static function fetchingType(): array
    {
        return [
            self::TYPE_DIAL,
            self::TYPE_QUEUE,
            self::TYPE_HANGUP
        ];
    }
}
