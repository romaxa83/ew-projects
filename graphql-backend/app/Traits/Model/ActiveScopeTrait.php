<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property bool active
 *
 * @see ActiveScopeTrait::scopeActive()
 * @method static active(bool $value = true)
 */
trait ActiveScopeTrait
{
    public function scopeActive(Builder|self $b, bool $value = true): void
    {
        $b->where(static::TABLE . '.active', $value);
    }
}
