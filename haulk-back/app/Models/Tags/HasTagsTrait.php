<?php

namespace App\Models\Tags;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @see static::tags()
 * @property Tag[]|Collection tags
 */
trait HasTagsTrait
{
    public function tags():MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
