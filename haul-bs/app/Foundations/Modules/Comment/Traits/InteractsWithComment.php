<?php

namespace App\Foundations\Modules\Comment\Traits;

use App\Foundations\Modules\Comment\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @see static::comments()
 * @property Comment[]|Collection comments
 */
trait InteractsWithComment
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'model');
    }
}
