<?php

namespace App\Models;

use Database\Factories\Materials\MaterialFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Material
 *
 * @property int $id
 * @property int $group_id
 * @property string $title
 * @property string|null $notes
 * @property string $price
 * @property int $need_packing
 * @property int $need_unpacking
 * @property string|null $packing_price
 * @property string|null $unpacking_price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \App\Models\Material\Group $group
 * @method static \Illuminate\Database\Eloquent\Builder|Material newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Material newQuery()
 * @method static \Illuminate\Database\Query\Builder|Material onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Material query()
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereNeedPacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereNeedUnpacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material wherePackingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereUnpackingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Material withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Material withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property int|null $division_id
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereDivisionId($value)
 * @property int|null $sort
 * @method static \Illuminate\Database\Eloquent\Builder|Material whereSort($value)
 * @mixin \Eloquent
 * @method static MaterialFactory factory(...$parameters)
 */
class Material extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const MORPH_NAME = 'material';

    public const TABLE = 'extra_materials';
    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'division_id',
        'group_id',
        'group',
        'need_packing',
        'need_unpacking',
        'packing_price',
        'unpacking_price',
        'price',
        'notes',
        'sort'
    ];
    protected $dates = ['deleted_at'];

    protected static function newFactory(): MaterialFactory
    {
        return MaterialFactory::new();
    }

    public function group()
    {
        return $this->hasOne(Material\Group::class, 'id', 'group_id')
            ->withDefault([
                'title' => 'Deleted category',
            ]);
    }

    public function setGroupAttribute($value)
    {
        $this->attributes['group_id'] = $value;
    }
}
