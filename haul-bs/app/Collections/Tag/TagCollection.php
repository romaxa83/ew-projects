<?php

namespace App\Collections\Tag;

use App\Models\Tags\Tag;
use ArrayIterator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Tag|null first(callable $callback = null, $default = null)
 * @method Tag|null last(callable $callback = null, $default = null)
 * @method Tag|null pop()
 * @method Tag|null shift()
 * @method ArrayIterator|Tag[] getIterator()
 */
class TagCollection extends Collection
{
    public function getNamesAsString(): string
    {
        $names = [];

        foreach ($this->items as $tag) {
            $names[] = $tag->name;
        }

        return implode(', ', $names);
    }
}
