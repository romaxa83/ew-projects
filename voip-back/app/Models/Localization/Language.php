<?php

namespace App\Models\Localization;

use App\Models\BaseModel;
use App\Traits\QueryCacheable;
use Carbon\Carbon;
use Database\Factories\Localization\LanguageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Lang;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $default Language by default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereDefault($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereName($value)
 * @method static Builder|self whereSlug($value)
 * @method static Builder|self whereUpdatedAt($value)
 *
 * @see Language::scopeDefault()
 * @method static Builder|self default()
 *
 * @method static LanguageFactory factory(int $number = null)
 */
class Language extends BaseModel
{
    use QueryCacheable;

    public const TABLE = 'languages';
    protected $table = self::TABLE;

    public static array $links = [];

    protected $fillable = [
        'name',
        'slug',
    ];
    protected $casts = [
        'default' => 'boolean',
    ];

    public function scopeDefault(Builder|self $build): void
    {
        $build->whereDefault(true);
    }

    public function setAsDefault(): bool
    {
        static::whereDefault(true)->update(
            [
                'default' => false,
            ]
        );

        $this->default = true;

        return $this->save();
    }

    public function getIsCurrentAttribute(): bool
    {
        return $this->slug === Lang::getLocale();
    }
}
