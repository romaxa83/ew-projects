<?php

namespace App\Models\Material;

use Database\Factories\Materials\GroupFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};

/**
 * App\Models\Material\Group
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Query\Builder|Group onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Group withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Group withoutTrashed()
 * @mixin \Eloquent
 * @method static GroupFactory factory(...$parameters)
 */
class Group extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'extra_materials_types';
    protected $table = self::TABLE;

    protected $fillable = ['title'];
    protected $dates = ['deleted_at'];

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }
}
