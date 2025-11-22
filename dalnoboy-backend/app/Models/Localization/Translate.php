<?php

namespace App\Models\Localization;

use App\Filters\Localization\TranslateFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translate extends BaseModel
{
    use HasFactory;
    use Filterable;
    use QueryCacheable;

    public const TABLE = 'translates';

    public const AVAILABLE_SORT_FIELDS = [
        'key',
        'text',
        'lang'
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'place',
        'key',
        'text',
        'lang'
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TranslateFilter::class);
    }

    public function language(): BelongsTo|Language
    {
        return $this->belongsTo(Language::class, 'lang', 'slug');
    }
}
