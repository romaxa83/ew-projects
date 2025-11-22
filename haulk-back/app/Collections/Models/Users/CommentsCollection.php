<?php

namespace App\Collections\Models\Users;

use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Illuminate\Database\Eloquent\Collection;

class CommentsCollection extends Collection implements DiffableInterface
{
    use Diffable;

    public function getAttributesForDiff(): array
    {
        $resultGetDirty = [];

        foreach ($this->items as $diffable) {
            $resultGetDirty[$diffable->id] = $diffable->getAttributesForDiff();
        }

        return $resultGetDirty;
    }
}
