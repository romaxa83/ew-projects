<?php

namespace App\Models\Localization;

use App\Models\BaseModel;
use App\Traits\QueryCacheable;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Language
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $default Language by default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder whereDefault($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereName($value)
 * @method static Builder|self whereSlug($value)
 * @method static Builder|self whereUpdatedAt($value)
 *
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 *
 * @see Language::scopeDefault()
 * @method static Builder|self default()
 * @method static Builder|self defaultAdmin()
 *
 * @mixin Eloquent
 */
class Language extends BaseModel
{
    use QueryCacheable;

    public const TABLE_NAME = 'languages';

    protected $table = self::TABLE_NAME;

    public const DEFAULT_ADMIN = 'ru';
    public const DEFAULT_FOR_COPY = 'ru';

    protected $fillable = [
        'name',
        'slug',
        'locale',
    ];

    protected $casts = [
        'default' => 'boolean',
    ];

    public function scopeDefault(Builder $build)
    {
        $build->whereDefault(true);
    }

    public function scopeDefaultAdmin(Builder $build)
    {
        $build->where('slug' , self::DEFAULT_ADMIN);
    }

}
