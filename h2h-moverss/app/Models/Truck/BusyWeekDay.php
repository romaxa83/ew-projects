<?php

namespace App\Models\Truck;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Truck\BusyWeekDay
 *
 * @property int $id
 * @property int $truck_id
 * @property int $user_id
 * @property mixed|null $miscs
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay query()
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereTruckId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusyWeekDay whereUserId($value)
 * @mixin \Eloquent
 */
class BusyWeekDay extends Model
{

    protected $table = 'trucks_busy_w_days';
    protected $casts = [
        'miscs' => 'json',
    ];
    protected $fillable = ['user_id', 'miscs'];

}
