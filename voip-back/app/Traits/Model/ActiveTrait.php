<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property bool active
 *
 * @see ActiveTrait::isActive()
 * @method isActive()
 *
 * @see ActiveTrait::scopeActive()
 * @method static active(bool $value = true)
 */
trait ActiveTrait
{
    public function isActive(): bool
    {
        return $this->active;
    }

    public function scopeActive(Builder|self $b, bool $value = true): void
    {
        $b->where(static::TABLE . '.active', $value);
    }
}

