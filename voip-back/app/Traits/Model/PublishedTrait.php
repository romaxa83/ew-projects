<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property bool active

 * @see PublishedTrait::scopePublished()
 * @method static published(bool $value = true)
 */
trait PublishedTrait
{
    public function scopePublished(Builder|self $b): void
    {
        $b->whereNotNull(static::TABLE . '.published_at');
    }
}
