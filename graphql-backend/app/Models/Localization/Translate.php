<?php

namespace App\Models\Localization;

use App\Filters\Localization\TranslateSimpleFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\QueryCacheable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property string place
 * @property string key
 * @property string text
 * @property string lang
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read Language language
 *
 * @method static static|Translate find($id)
 */
class Translate extends BaseModel
{
    use HasFactory;
    use Filterable;
    use QueryCacheable;

    public const TABLE = 'translates';

    protected $table = self::TABLE;

    protected $fillable = [
        'place',
        'key',
        'text',
        'lang'
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TranslateSimpleFilter::class);
    }

    public function language(): BelongsTo|Language
    {
        return $this->belongsTo(Language::class, 'lang', 'slug');
    }
}
