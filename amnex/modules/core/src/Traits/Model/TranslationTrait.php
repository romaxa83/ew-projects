<?php

namespace Wezom\Core\Traits\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

trait TranslationTrait
{
    public function row(): BelongsTo
    {
        return $this->belongsTo($this->relatedModelName(), 'row_id')->withDefault();
    }

    public function relatedModelName(): string
    {
        return Str::replaceLast('Translation', '', static::class);
    }
}
