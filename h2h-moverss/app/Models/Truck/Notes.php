<?php

namespace App\Models\Truck;

use Database\Factories\Trucks\NoteFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};

/**
 * App\Models\Truck\Notes
 *
 * @property int $id
 * @property int $truck_id
 * @property int $user_id
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes newQuery()
 * @method static \Illuminate\Database\Query\Builder|Notes onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereTruckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notes whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|Notes withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Notes withoutTrashed()
 * @mixin \Eloquent
 * @method static NoteFactory factory(...$parameters)
 */
class Notes extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TABLE = 'trucks_notes';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'value',
        'user_id'
    ];

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }
}
