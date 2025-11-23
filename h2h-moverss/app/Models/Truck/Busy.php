<?php

namespace App\Models\Truck;

use Illuminate\Database\Eloquent\{SoftDeletes, Model};

/**
 * App\Models\Truck\Busy
 *
 * @property int $id
 * @property int $truck_id
 * @property int $user_id
 * @property \datetime $start_date
 * @property \datetime $end_date
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Busy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Busy newQuery()
 * @method static \Illuminate\Database\Query\Builder|Busy onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Busy query()
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereTruckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Busy whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Busy withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Busy withoutTrashed()
 * @mixin \Eloquent
 */
class Busy extends Model
{

    use SoftDeletes;

    protected $table = 'trucks_busy';
    protected $dates = ['deleted_at', 'start_date', 'end_date'];
    protected $fillable = ['user_id', 'start_date', 'end_date', 'reason'];
    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
    ];

}
