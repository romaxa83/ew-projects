<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property bool notify
 *
 * @see NotifyTrait::isNotify()
 * @method isNotify()
 *
 * @see NotifyTrait::scopeNotify()
 * @method static notify(bool $value = true)
 */
trait NotifyTrait
{
    public function isNotify(): bool
    {
        return $this->notify;
    }

    public function scopeNotify(Builder|self $b, bool $value = true): void
    {
        $b->where(static::TABLE . '.notify', $value);
    }
}


