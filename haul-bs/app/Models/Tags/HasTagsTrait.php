<?php

namespace App\Models\Tags;

use App\Collections\Tag\TagCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @see static::tags()
 * @property Tag[]|TagCollection tags
 */
trait HasTagsTrait
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
