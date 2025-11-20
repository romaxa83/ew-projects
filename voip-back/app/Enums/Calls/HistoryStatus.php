<?php

namespace App\Enums\Calls;

use Core\Enums\BaseGqlEnum;

/**
 * @method static static CANCEL()       // Вызов отменен
 * @method static static ANSWERED()     // На вызов был получен ответ
 * @method static static NO_ANSWER()    // На вызов не ответили
 * @method static static BUSY()         // Получен сигнал занято
 * @method static static CONGESTION()   // Канал перегружен
 * @method static static CHANUNAVAIL()  // Канал недоступен (Для SIP, может быть в случае если пир не зарегистрирован)
 * @method static static TRANSFER()
 * @method static static CRM_TRANSFER()
 */
class HistoryStatus extends BaseGqlEnum
{
    public const CANCEL      = 'cancel';
    public const ANSWERED    = 'answered';
    public const NO_ANSWER   = 'no_answer';
    public const BUSY        = 'busy';
    public const CONGESTION  = 'congestion';
    public const CHANUNAVAIL = 'chanunavail';
    public const TRANSFER    = 'transfer';
    public const CRM_TRANSFER    = 'crm_transfer';

    public function isCancel(): bool
    {
        return $this->is(self::CANCEL());
    }

    public function isAnswered(): bool
    {
        return $this->is(self::ANSWERED());
    }

    public function isNoAnswer(): bool
    {
        return $this->is(self::NO_ANSWER());
    }

    public function isBusy(): bool
    {
        return $this->is(self::BUSY());
    }

    public function isCongestion(): bool
    {
        return $this->is(self::CONGESTION());
    }

    public function isTransfer(): bool
    {
        return $this->is(self::TRANSFER());
    }

    public function isChanunavail(): bool
    {
        return $this->is(self::CHANUNAVAIL());
    }
}

