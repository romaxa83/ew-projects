<?php

namespace App\Foundations\Traits\Models;

/**
 * @see static::getFullNameAttribute()
 * @property-read string $full_name
 */
trait FullNameTrait
{
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
