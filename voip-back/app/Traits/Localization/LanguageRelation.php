<?php

namespace App\Traits\Localization;

use App\Models\Localization\Language;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string lang
 *
 * @property-read Language language
 */
trait LanguageRelation
{

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang', 'slug');
    }
}
