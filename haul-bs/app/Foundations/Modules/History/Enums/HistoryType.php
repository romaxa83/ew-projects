<?php

namespace App\Foundations\Modules\History\Enums;

use App\Foundations\Enums\BaseEnum;

/**
 * @method static static ACTIVITY()
 * @method static static CHANGES()
 */

class HistoryType extends BaseEnum
{
    public const ACTIVITY = 'activity';
    public const CHANGES = 'changes';

    public function isActivity(): bool
    {
        return $this->is(self::ACTIVITY());
    }

    public function isChanges(): bool
    {
        return $this->is(self::CHANGES());
    }
}
