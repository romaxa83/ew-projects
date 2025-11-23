<?php

namespace App\Models;

use Database\Factories\Orders\ParkingTypeFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, SoftDeletes};

/**
 * App\Models\ParkingType
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType newQuery()
 * @method static \Illuminate\Database\Query\Builder|ParkingType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParkingType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ParkingType withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ParkingType withoutTrashed()
 * @mixin \Eloquent
 * @method static ParkingTypeFactory factory(...$parameters)
 */

// todo удалить после перехода на enum
class ParkingType extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const TABLE = 'parking_types';
    protected $table = self::TABLE;

    protected $dates = ['deleted_at'];

    protected static function newFactory(): ParkingTypeFactory
    {
        return ParkingTypeFactory::new();
    }
}
