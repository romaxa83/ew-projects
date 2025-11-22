<?php

namespace App\Foundations\Modules\History\Traits;

use App\Foundations\Modules\History\Models\History;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @see static::histories()
 * @property History[]|Collection histories
 */
trait InteractsWithHistory
{
    public function histories(): MorphMany
    {
        return $this->morphMany(History::class, 'model')
            ->latest('performed_at');
    }

    public function dataForUpdateHistory(): array
    {
        return $this->getAttributes();
    }
}
