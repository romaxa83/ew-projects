<?php

namespace App\Foundations\Modules\Localization\Models;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Localization\Enums\Translations\TranslationPlace;
use App\Foundations\Modules\Localization\Factories\TranslationFactory;
use App\Foundations\Modules\Localization\Filters\TranslationFilter;
use App\Foundations\Traits\Filters\Filterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int id
 * @property TranslationPlace place
 * @property string key
 * @property string text
 * @property string lang
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read Language language
 *
 * @method static TranslationFactory factory(...$options)
 */
class Translation extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'translations';

    protected $table = self::TABLE;

    protected $fillable = [
        'place',
        'key',
        'text',
        'lang'
    ];

    public const ALLOWED_SORTING_FIELDS = [
        'lang',
        'key',
        'place',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'place' => TranslationPlace::class,
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(TranslationFilter::class);
    }

    protected static function newFactory(): TranslationFactory
    {
        return TranslationFactory::new();
    }
}
