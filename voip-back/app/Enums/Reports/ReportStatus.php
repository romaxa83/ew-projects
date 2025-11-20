<?php

namespace App\Enums\Reports;

use Core\Enums\BaseGqlEnum;

/**
 * @method static static ANSWERED()     // На вызов был получен ответ
 * @method static static NO_ANSWER()    // На вызов не ответили
 * @method static static TRANSFER()
 */
class ReportStatus extends BaseGqlEnum
{
    public const ANSWERED    = 'answered';
    public const NO_ANSWER   = 'no_answer';
    public const TRANSFER    = 'transfer';

    public function isAnswered(): bool
    {
        return $this->is(self::ANSWERED());
    }

    public function isNoAnswer(): bool
    {
        return $this->is(self::NO_ANSWER());
    }

    public function isTransfer(): bool
    {
        return $this->is(self::TRANSFER());
    }

    public function prettyValue(): string
    {
        return mb_strtolower(prettyStr($this->value));
    }
}
