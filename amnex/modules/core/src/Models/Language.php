<?php

declare(strict_types=1);

namespace Wezom\Core\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;

/**
 * \Wezom\Core\Models\Language
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $is_current
 * @method static Builder<static>|Language default()
 * @method static Builder<static>|Language newModelQuery()
 * @method static Builder<static>|Language newQuery()
 * @method static Builder<static>|Language query()
 * @method static Builder<static>|Language whereCreatedAt($value)
 * @method static Builder<static>|Language whereDefault($value)
 * @method static Builder<static>|Language whereId($value)
 * @method static Builder<static>|Language whereName($value)
 * @method static Builder<static>|Language whereSlug($value)
 * @method static Builder<static>|Language whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Language extends Model
{
    public const string UK = 'uk';
    public const string RU = 'ru';
    public const string EN = 'en';

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
