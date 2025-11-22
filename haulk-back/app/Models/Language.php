<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Language
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $default Language by default
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static Builder|Language whereCreatedAt($value)
 * @method static Builder|Language whereDefault($value)
 * @method static Builder|Language whereId($value)
 * @method static Builder|Language whereName($value)
 * @method static Builder|Language whereSlug($value)
 * @method static Builder|Language whereUpdatedAt($value)
 * @method static Builder|Language default()
 * @method static Builder|Language newModelQuery()
 * @method static Builder|Language newQuery()
 * @method static Builder|Language query()
 * @mixin Eloquent
 */
class Language extends Model
{
    public const TABLE_NAME = 'languages';

    public $timestamps = false;

    public $table = 'languages';

    protected $fillable = [
        'name',
        'slug',
        'default'
    ];

    protected $casts = ['default' => 'boolean'];

    /**
     * @param Builder|self $build
     */
    public function scopeDefault($build)
    {
        $build->whereDefault(true);
    }
}
