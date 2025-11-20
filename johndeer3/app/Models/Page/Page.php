<?php

namespace App\Models\Page;

use App\Helpers\ConvertLangToLocale;
use App\ModelFilters\Page\PageFilter;
use App\Traits\ActiveTrait;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $alias
 * @property boolean $active
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Collection|PageTranslation[] translations
 * @property-read PageTranslation current
 */

class Page extends Model
{
    use Filterable;
    use ActiveTrait;
    use HasFactory;

    const ALIAS_AGREEMENT = 'agreement';
    const ALIAS_PRIVATE_POLICY = 'private-policy';
    const ALIAS_DISCLAIMER = 'disclaimer';

    const TABLE = 'pages';
    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'boolean'
    ];

    public static function aliasList(): array
    {
        return [
            self::ALIAS_AGREEMENT => self::ALIAS_AGREEMENT,
            self::ALIAS_PRIVATE_POLICY => self::ALIAS_PRIVATE_POLICY,
            self::ALIAS_DISCLAIMER => self::ALIAS_DISCLAIMER
        ];
    }

    public function modelFilter()
    {
        return $this->provideFilter(PageFilter::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class, 'page_id', 'id');
    }

    public function current(): HasOne
    {
        return $this->hasOne(PageTranslation::class,'page_id', 'id')
            ->where('lang', ConvertLangToLocale::convert(\App::getLocale()));
    }
}
