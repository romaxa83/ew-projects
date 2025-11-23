<?php

namespace App\Models;

use Database\Factories\Work\WorkTypeFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\WorkTypes
 *
 * @property int $id
 * @property int $sort
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes newQuery()
 * @method static \Illuminate\Database\Query\Builder|WorkTypes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes query()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkTypes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|WorkTypes withTrashed()
 * @method static \Illuminate\Database\Query\Builder|WorkTypes withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 * @method static WorkTypeFactory factory(...$parameters)
 */
class WorkTypes extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'works_types';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected static function newFactory(): WorkTypeFactory
    {
        return WorkTypeFactory::new();
    }
}
