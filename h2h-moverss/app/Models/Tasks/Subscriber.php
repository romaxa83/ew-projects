<?php

namespace App\Models\Tasks;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tasks\Subscriber
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber query()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereUserId($value)
 * @mixin \Eloquent
 */
class Subscriber extends Model
{
    protected $table = 'tasks_subscribers';
    public $timestamps = false;
    protected $fillable = ['user_id'];
}
