<?php

namespace App\Foundations\Modules\Localization\Models;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Localization\Factories\LanguageFactory;
use App\Foundations\Modules\Localization\Filters\LanguageFilter;
use App\Foundations\Traits\Filters\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $native
 * @property bool $default Language by default
 * @property bool $active
 * @property int $sort
 *
 * @see Language::getIsCurrentAttribute()
 * @property-read bool is_current
 *
 * @see Language::scopeDefault()
 * @method static Builder|self default()
 *
 * @method static LanguageFactory factory(...$parameters)
 */
class Language extends BaseModel
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'languages';
    protected $table = self::TABLE;

    public $timestamps = false;

    public static array $links = [];

    protected $fillable = [
        'active',
        'default',
    ];

    protected $casts = [
        'default' => 'boolean',
        'active' => 'boolean',
    ];

    public const ALLOWED_SORTING_FIELDS = [
        'sort',
        'name',
    ];

    public function modelFilter(): string
    {
        return LanguageFilter::class;
    }

    protected static function newFactory(): LanguageFactory
    {
        return LanguageFactory::new();
    }
}

