<?php

namespace App\Models\Tasks;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Tasks\StatusHistory
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property int $prev_status
 * @property int $new_status
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory whereNewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory wherePrevStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StatusHistory whereUserId($value)
 * @mixin \Eloquent
 */
class StatusHistory extends Model
{
    protected $table = 'tasks_status_history';
    public $timestamps = false;
    protected $fillable = ['user_id', 'prev_status', 'new_status', 'created_at'];
}
