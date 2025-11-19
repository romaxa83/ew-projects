<?php

declare(strict_types=1);

namespace Wezom\Core\Media\Collections;

use Illuminate\Database\Eloquent\Collection;

/**
 * @extends \Illuminate\Database\Eloquent\Collection<int, \Wezom\Core\Models\Media>
 */
class MediaCollection extends Collection
{
    public function whereCollection(string $name): static
    {
        return $this->where('collection_name', $name);
    }
}
